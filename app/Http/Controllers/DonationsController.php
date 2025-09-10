<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class DonationsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager') && !$user->hasRole('Viewer')) {
            abort(403, 'Unauthorized.');
        }
        $query = Donation::with('member');
        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }
        $donations = $query->latest()->paginate(20);
        $members = Member::all();
        return view('donations', compact('donations', 'members'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager')) {
            abort(403, 'Unauthorized.');
        }
        $members = Member::all();
        return view('donations_create', compact('members'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager')) {
            abort(403, 'Unauthorized.');
        }
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
        ]);
        Donation::create($request->only('member_id', 'amount'));
        return redirect()->route('donations.index')->with('success', 'Donation added successfully.');
    }

    public function edit(Donation $donation)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager')) {
            abort(403, 'Unauthorized.');
        }
        $members = Member::all();
        return view('donations_edit', compact('donation', 'members'));
    }

    public function update(Request $request, Donation $donation)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager')) {
            abort(403, 'Unauthorized.');
        }
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
        ]);
        $donation->update($request->only('member_id', 'amount'));
        return redirect()->route('donations.index')->with('success', 'Donation updated successfully.');
    }

    public function destroy(Donation $donation)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager')) {
            abort(403, 'Unauthorized.');
        }
        $donation->delete();
        return redirect()->route('donations.index')->with('success', 'Donation deleted successfully.');
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager') && !$user->hasRole('Viewer')) {
            abort(403, 'Unauthorized.');
        }
        $query = Donation::with('member');
        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }
        $donations = $query->latest()->get();
        $response = new StreamedResponse(function () use ($donations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Member', 'Amount', 'Date']);
            foreach ($donations as $donation) {
                fputcsv($handle, [
                    $donation->member->name ?? 'Anonymous',
                    $donation->amount,
                    $donation->created_at->toDateString(),
                ]);
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="donations.csv"');
        return $response;
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Donations Manager') && !$user->hasRole('Viewer')) {
            abort(403, 'Unauthorized.');
        }
        $query = Donation::with('member');
        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->min_amount) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->max_amount) {
            $query->where('amount', '<=', $request->max_amount);
        }
        $donations = $query->latest()->get();
        $pdf = Pdf::loadView('donations_pdf', compact('donations'));
        return $pdf->download('donations.pdf');
    }
}
