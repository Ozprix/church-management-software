<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Member;
use App\Models\TaxReceipt;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;

class TaxReceiptService
{
    /**
     * Generate a tax receipt for a specific donation.
     *
     * @param Donation $donation
     * @return TaxReceipt|null
     */
    public function generateReceiptForDonation(Donation $donation): ?TaxReceipt
    {
        // Check if donation is eligible for a tax receipt
        if (!$this->isDonationEligibleForReceipt($donation)) {
            Log::info('Donation not eligible for tax receipt', ['donation_id' => $donation->id]);
            return null;
        }
        
        // Check if receipt already exists
        $existingReceipt = TaxReceipt::where('donation_id', $donation->id)->first();
        if ($existingReceipt) {
            Log::info('Tax receipt already exists for donation', ['donation_id' => $donation->id, 'receipt_id' => $existingReceipt->id]);
            return $existingReceipt;
        }
        
        try {
            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber($donation);
            
            // Get organization details
            $organization = $this->getOrganizationDetails();
            
            // Create receipt record
            $receipt = new TaxReceipt([
                'donation_id' => $donation->id,
                'member_id' => $donation->member_id,
                'receipt_number' => $receiptNumber,
                'amount' => $donation->amount,
                'donation_date' => $donation->donation_date,
                'issue_date' => Carbon::now(),
                'tax_year' => $donation->donation_date->year,
                'status' => 'issued',
            ]);
            
            // Generate PDF only if not already present
            $filePath = 'tax_receipts/' . $donation->donation_date->year . '/' . $receiptNumber . '.pdf';
            if (!Storage::disk('public')->exists($filePath)) {
                $pdfContent = $this->generateReceiptPDF($donation, $receipt, $organization);
                Storage::disk('public')->put($filePath, $pdfContent);
            }
            
            // Update receipt with file path
            $receipt->file_path = $filePath;
            $receipt->save();
            
            // Update donation with receipt information
            $donation->receipt_sent = true;
            $donation->receipt_sent_at = Carbon::now();
            $donation->save();
            
            return $receipt;
        } catch (\Exception $e) {
            Log::error('Failed to generate tax receipt', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Generate annual tax receipts for a member for a specific year.
     *
     * @param Member $member
     * @param int $year
     * @return TaxReceipt|null
     */
    public function generateAnnualReceiptForMember(Member $member, int $year): ?TaxReceipt
    {
        try {
            // Get all eligible donations for the year that don't already have annual receipts
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
            
            $donations = Donation::where('member_id', $member->id)
                ->whereBetween('donation_date', [$startDate, $endDate])
                ->where('payment_status', 'completed')
                ->whereDoesntHave('annualTaxReceipt')
                ->get();
            
            if ($donations->isEmpty()) {
                Log::info('No eligible donations found for annual tax receipt', [
                    'member_id' => $member->id,
                    'year' => $year
                ]);
                return null;
            }
            
            // Calculate total amount
            $totalAmount = $donations->sum('amount');
            
            // Generate receipt number
            $receiptNumber = $this->generateAnnualReceiptNumber($member, $year);
            
            // Get organization details
            $organization = $this->getOrganizationDetails();
            
            // Create receipt record
            $receipt = new TaxReceipt([
                'member_id' => $member->id,
                'receipt_number' => $receiptNumber,
                'amount' => $totalAmount,
                'issue_date' => Carbon::now(),
                'tax_year' => $year,
                'is_annual' => true,
                'status' => 'issued',
            ]);
            
            $receipt->save();
            
            // Associate donations with this receipt
            foreach ($donations as $donation) {
                $donation->annual_tax_receipt_id = $receipt->id;
                $donation->save();
            }
            
            // Generate PDF
            $pdfContent = $this->generateAnnualReceiptPDF($member, $donations, $receipt, $organization, $year);
            
            // Save PDF to storage
            $filePath = 'tax_receipts/annual/' . $year . '/' . $receiptNumber . '.pdf';
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Update receipt with file path
            $receipt->file_path = $filePath;
            $receipt->save();
            
            return $receipt;
        } catch (\Exception $e) {
            Log::error('Failed to generate annual tax receipt', [
                'member_id' => $member->id,
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Generate annual tax receipts for all members for a specific year.
     *
     * @param int $year
     * @return array
     */
    public function generateAllAnnualReceipts(int $year): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // Get all members who have eligible donations for the year
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
        
        $members = Member::whereHas('donations', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('donation_date', [$startDate, $endDate])
                ->where('payment_status', 'completed')
                ->whereNull('annual_tax_receipt_id');
        })->get();
        
        $results['total'] = $members->count();
        
        foreach ($members as $member) {
            try {
                $receipt = $this->generateAnnualReceiptForMember($member, $year);
                
                if ($receipt) {
                    $results['success']++;
                    $results['details'][] = [
                        'member_id' => $member->id,
                        'member_name' => $member->full_name,
                        'receipt_id' => $receipt->id,
                        'receipt_number' => $receipt->receipt_number,
                        'status' => 'success'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'member_id' => $member->id,
                        'member_name' => $member->full_name,
                        'status' => 'failed',
                        'reason' => 'No eligible donations or failed to generate receipt'
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'member_id' => $member->id,
                    'member_name' => $member->full_name,
                    'status' => 'failed',
                    'reason' => $e->getMessage()
                ];
                
                Log::error('Failed to generate annual tax receipt for member', [
                    'member_id' => $member->id,
                    'year' => $year,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * Send a tax receipt to a member via email.
     *
     * @param TaxReceipt $receipt
     * @return bool
     */
    public function sendReceiptByEmail(TaxReceipt $receipt): bool
    {
        try {
            $member = $receipt->member;
            
            if (!$member || !$member->email) {
                Log::error('Cannot send tax receipt: member has no email', [
                    'receipt_id' => $receipt->id,
                    'member_id' => $receipt->member_id
                ]);
                return false;
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($receipt->file_path)) {
                Log::error('Cannot send tax receipt: file not found', [
                    'receipt_id' => $receipt->id,
                    'file_path' => $receipt->file_path
                ]);
                return false;
            }
            
            // Send email with attachment
            $organization = $this->getOrganizationDetails();
            
            \Mail::send('emails.tax-receipt', [
                'member' => $member,
                'receipt' => $receipt,
                'organization' => $organization
            ], function ($message) use ($member, $receipt, $organization) {
                $message->to($member->email, $member->full_name)
                    ->subject($receipt->is_annual 
                        ? 'Your ' . $receipt->tax_year . ' Annual Tax Receipt from ' . $organization->name
                        : 'Tax Receipt #' . $receipt->receipt_number . ' from ' . $organization->name);
                    
                $message->attach(Storage::disk('public')->path($receipt->file_path));
            });
            
            // Update receipt status
            $receipt->status = 'sent';
            $receipt->sent_at = Carbon::now();
            $receipt->save();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send tax receipt by email', [
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Void a tax receipt.
     *
     * @param TaxReceipt $receipt
     * @param string $reason
     * @return bool
     */
    public function voidReceipt(TaxReceipt $receipt, string $reason): bool
    {
        try {
            // Update receipt status
            $receipt->status = 'voided';
            $receipt->void_reason = $reason;
            $receipt->voided_at = Carbon::now();
            $receipt->save();
            
            // If it's a donation-specific receipt, update the donation
            if ($receipt->donation_id) {
                $donation = $receipt->donation;
                $donation->receipt_sent = false;
                $donation->receipt_sent_at = null;
                $donation->save();
            }
            
            // If it's an annual receipt, clear the association with donations
            if ($receipt->is_annual) {
                Donation::where('annual_tax_receipt_id', $receipt->id)
                    ->update(['annual_tax_receipt_id' => null]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to void tax receipt', [
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Check if a donation is eligible for a tax receipt.
     *
     * @param Donation $donation
     * @return bool
     */
    protected function isDonationEligibleForReceipt(Donation $donation): bool
    {
        // Check if the donation payment is completed
        if ($donation->payment_status !== 'completed') {
            return false;
        }
        
        // Check if the donation has a member
        if (!$donation->member_id) {
            return false;
        }
        
        // Check if the donation already has a receipt
        if ($donation->receipt_sent) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate a unique receipt number for a donation.
     *
     * @param Donation $donation
     * @return string
     */
    protected function generateReceiptNumber(Donation $donation): string
    {
        $prefix = 'TR-';
        $year = $donation->donation_date->format('Y');
        $sequence = TaxReceipt::where('tax_year', $year)
            ->where('is_annual', false)
            ->count() + 1;
        
        return $prefix . $year . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a unique annual receipt number for a member.
     *
     * @param Member $member
     * @param int $year
     * @return string
     */
    protected function generateAnnualReceiptNumber(Member $member, int $year): string
    {
        $prefix = 'AR-';
        $sequence = TaxReceipt::where('tax_year', $year)
            ->where('is_annual', true)
            ->count() + 1;
        
        return $prefix . $year . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get organization details for tax receipts.
     *
     * @return object
     */
    protected function getOrganizationDetails(): object
    {
        // Try to get from database if Organization model exists
        $organization = Organization::first();
        
        if ($organization) {
            return $organization;
        }
        
        // Otherwise, return default values from config
        return (object) [
            'name' => config('app.organization_name', 'Church Management System'),
            'address' => config('app.organization_address', '123 Main Street, City, State, ZIP'),
            'phone' => config('app.organization_phone', '(555) 123-4567'),
            'email' => config('app.organization_email', 'info@churchmanagement.com'),
            'website' => config('app.organization_website', 'www.churchmanagement.com'),
            'tax_id' => config('app.organization_tax_id', '12-3456789'),
            'logo_path' => config('app.organization_logo', 'images/logo.png'),
        ];
    }
    
    /**
     * Generate a PDF for a single donation tax receipt.
     *
     * @param Donation $donation
     * @param TaxReceipt $receipt
     * @param object $organization
     * @return string
     */
    protected function generateReceiptPDF(Donation $donation, TaxReceipt $receipt, object $organization): string
    {
        $data = [
            'donation' => $donation,
            'receipt' => $receipt,
            'member' => $donation->member,
            'organization' => $organization,
        ];
        
        $pdf = PDF::loadView('tax_receipts.single', $data);
        
        return $pdf->output();
    }
    
    /**
     * Generate a PDF for an annual tax receipt.
     *
     * @param Member $member
     * @param \Illuminate\Support\Collection $donations
     * @param TaxReceipt $receipt
     * @param object $organization
     * @param int $year
     * @return string
     */
    protected function generateAnnualReceiptPDF(Member $member, $donations, TaxReceipt $receipt, object $organization, int $year): string
    {
        // Group donations by category
        $donationsByCategory = $donations->groupBy('category_id');
        $categorizedDonations = [];
        
        foreach ($donationsByCategory as $categoryId => $categoryDonations) {
            $categoryName = $categoryId ? $categoryDonations->first()->category->name : 'Uncategorized';
            $categoryTotal = $categoryDonations->sum('amount');
            
            $categorizedDonations[] = [
                'name' => $categoryName,
                'total' => $categoryTotal,
                'donations' => $categoryDonations
            ];
        }
        
        $data = [
            'member' => $member,
            'receipt' => $receipt,
            'donations' => $donations,
            'categorized_donations' => $categorizedDonations,
            'organization' => $organization,
            'year' => $year,
            'total_amount' => $donations->sum('amount'),
        ];
        
        $pdf = PDF::loadView('tax_receipts.annual', $data);
        
        return $pdf->output();
    }
}
