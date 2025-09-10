<?php

namespace App\Exports;

use App\Models\Donation;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DonationsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $categoryId
     */
    public function __construct(Carbon $startDate, Carbon $endDate, ?int $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Donation::with(['member', 'category', 'project'])
            ->whereBetween('donation_date', [$this->startDate, $this->endDate])
            ->where('payment_status', 'completed')
            ->orderBy('donation_date', 'desc');
            
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        
        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Receipt Number',
            'Donor',
            'Category',
            'Project',
            'Payment Method',
            'Payment Status',
            'Amount',
            'Is Anonymous',
            'Notes'
        ];
    }

    /**
     * @param Donation $donation
     * @return array
     */
    public function map($donation): array
    {
        return [
            $donation->id,
            $donation->donation_date->format('Y-m-d'),
            $donation->receipt_number,
            $donation->is_anonymous ? 'Anonymous' : ($donation->member ? $donation->member->full_name : 'N/A'),
            $donation->category ? $donation->category->name : 'N/A',
            $donation->project ? $donation->project->name : 'N/A',
            $donation->payment_method,
            $donation->payment_status,
            $donation->amount,
            $donation->is_anonymous ? 'Yes' : 'No',
            $donation->notes
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Donations';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style the amount column as currency
            'I' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
        ];
    }
}
