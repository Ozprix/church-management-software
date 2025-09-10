@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-primary-dark">Donations</h1>
    <a href="{{ route('donations.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">Add Donation</a>
</div>
<form method="GET" action="" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="block text-xs text-neutral-dark mb-1">Member</label>
        <select name="member_id" class="border border-neutral-dark rounded px-2 py-1">
            <option value="">All</option>
            @foreach($members as $member)
            <option value="{{ $member->id }}" @if(request('member_id')==$member->id) selected @endif>{{ $member->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-neutral-dark mb-1">Start Date</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-neutral-dark rounded px-2 py-1">
    </div>
    <div>
        <label class="block text-xs text-neutral-dark mb-1">End Date</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-neutral-dark rounded px-2 py-1">
    </div>
    <div>
        <label class="block text-xs text-neutral-dark mb-1">Min Amount</label>
        <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="border border-neutral-dark rounded px-2 py-1" min="0">
    </div>
    <div>
        <label class="block text-xs text-neutral-dark mb-1">Max Amount</label>
        <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="border border-neutral-dark rounded px-2 py-1" min="0">
    </div>
    <button type="submit" class="px-3 py-1 bg-primary text-white rounded hover:bg-primary-dark">Filter</button>
    <a href="{{ route('donations.export', request()->all()) }}" class="px-3 py-1 bg-success text-white rounded hover:bg-success/80">Export CSV</a>
    <a href="{{ route('donations.exportPdf', request()->all()) }}" class="px-3 py-1 bg-error text-white rounded hover:bg-error/80">Export PDF</a>
</form>
@if(session('success'))
<div class="mb-4 p-2 bg-success/10 text-success rounded">{{ session('success') }}</div>
@endif
<div class="bg-white rounded shadow p-6 overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Member</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Amount</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Date</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($donations as $donation)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $donation->member->name ?? 'Anonymous' }}</td>
                <td class="px-4 py-2 whitespace-nowrap">₦{{ number_format($donation->amount) }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $donation->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-2 whitespace-nowrap flex gap-2">
                    <a href="{{ route('donations.edit', $donation) }}" class="text-primary hover:underline">Edit</a>
                    <form action="{{ route('donations.destroy', $donation) }}" method="POST" onsubmit="return confirm('Delete this donation?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-2 text-center text-gray-400">No donations found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $donations->withQueryString()->links() }}</div>
</div>
@endsection