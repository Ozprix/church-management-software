@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-primary-dark">Donations</h1>
    <a href="{{ route('donations.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Donation
    </a>
</div>

<!-- Enhanced Filter Panel -->
<form method="GET" action="" class="mb-6 bg-white p-4 rounded-lg shadow">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
        <!-- Preset Date Ranges -->
        <div class="lg:col-span-2">
            <label class="block text-xs text-neutral-dark mb-1 font-medium">Quick Date Range</label>
            <select id="date-range-preset" class="border border-neutral-dark rounded px-2 py-1 w-full" onchange="applyDatePreset(this.value)">
                <option value="">Custom Range</option>
                <option value="today" {{ request('start_date') == date('Y-m-d') && request('end_date') == date('Y-m-d') ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('start_date') == date('Y-m-d', strtotime('monday this week')) && request('end_date') == date('Y-m-d', strtotime('sunday this week')) ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('start_date') == date('Y-m-01') && request('end_date') == date('Y-m-t') ? 'selected' : '' }}>This Month</option>
                <option value="quarter" {{ request('start_date') == date('Y-' . (floor((date('n')-1)/3)*3+1) . '-01') && request('end_date') == date('Y-' . (floor((date('n')-1)/3)*3+3) . '-t') ? 'selected' : '' }}>This Quarter</option>
                <option value="year" {{ request('start_date') == date('Y-01-01') && request('end_date') == date('Y-12-31') ? 'selected' : '' }}>This Year</option>
            </select>
        </div>
        
        <div>
            <label class="block text-xs text-neutral-dark mb-1 font-medium">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border border-neutral-dark rounded px-2 py-1 w-full">
        </div>
        <div>
            <label class="block text-xs text-neutral-dark mb-1 font-medium">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border border-neutral-dark rounded px-2 py-1 w-full">
        </div>
        <div>
            <label class="block text-xs text-neutral-dark mb-1 font-medium">Min Amount</label>
            <input type="number" name="min_amount" value="{{ request('min_amount') }}" class="border border-neutral-dark rounded px-2 py-1 w-full" min="0" placeholder="₦0">
        </div>
        <div>
            <label class="block text-xs text-neutral-dark mb-1 font-medium">Max Amount</label>
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" class="border border-neutral-dark rounded px-2 py-1 w-full" min="0" placeholder="₦Any">
        </div>
    </div>
    
    <div class="flex flex-wrap gap-2 items-center">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-neutral-dark mb-1 font-medium">Member</label>
            <select name="member_id" class="border border-neutral-dark rounded px-2 py-1 w-full">
                <option value="">All Members</option>
                @foreach($members as $member)
                <option value="{{ $member->id }}" @if(request('member_id')==$member->id) selected @endif>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
            <a href="{{ route('donations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                Clear
            </a>
            <a href="{{ route('donations.export', request()->all()) }}" class="px-4 py-2 bg-success text-white rounded hover:bg-success/80 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                CSV
            </a>
            <a href="{{ route('donations.exportPdf', request()->all()) }}" class="px-4 py-2 bg-error text-white rounded hover:bg-error/80 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>
</form>

@if(session('success'))
<div class="mb-4 p-4 bg-success/10 text-success rounded flex items-center gap-2" role="alert">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<!-- Summary Stats -->
@if($donations->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow">
        <p class="text-sm text-gray-600">Total Donations</p>
        <p class="text-2xl font-bold text-primary">{{ $donations->count() }}</p>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <p class="text-sm text-gray-600">Total Amount</p>
        <p class="text-2xl font-bold text-success">₦{{ number_format($donations->sum('amount')) }}</p>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <p class="text-sm text-gray-600">Average Donation</p>
        <p class="text-2xl font-bold text-blue-600">₦{{ number_format($donations->avg('amount'), 2) }}</p>
    </div>
</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral" role="table" aria-label="Donations table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortBy('member')" scope="col">
                        <div class="flex items-center gap-1">
                            Member
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortBy('amount')" scope="col">
                        <div class="flex items-center gap-1">
                            Amount
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortBy('date')" scope="col">
                        <div class="flex items-center gap-1">
                            Date
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral">
                @forelse($donations as $donation)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $donation->member->name ?? 'Anonymous' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-success">₦{{ number_format($donation->amount) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $donation->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('donations.edit', $donation) }}" class="text-primary hover:underline inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('donations.destroy', $donation) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this donation?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        No donations found. Try adjusting your filters or add a new donation.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($donations->hasPages())
    <div class="mt-4 px-4 py-3 border-t border-gray-200">
        {{ $donations->withQueryString()->links() }}
    </div>
    @endif
</div>

<script>
function applyDatePreset(preset) {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const today = new Date();
    
    switch(preset) {
        case 'today':
            startDate.value = formatDate(today);
            endDate.value = formatDate(today);
            break;
        case 'week':
            const monday = new Date(today);
            monday.setDate(today.getDate() - ((today.getDay() + 6) % 7));
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            startDate.value = formatDate(monday);
            endDate.value = format(sunday);
            break;
        case 'month':
            startDate.value = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
            endDate.value = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            startDate.value = formatDate(new Date(today.getFullYear(), quarter * 3, 1));
            endDate.value = formatDate(new Date(today.getFullYear(), (quarter + 1) * 3, 0));
            break;
        case 'year':
            startDate.value = formatDate(new Date(today.getFullYear(), 0, 1));
            endDate.value = formatDate(new Date(today.getFullYear(), 11, 31));
            break;
    }
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function sortBy(field) {
    // Simple client-side sorting indicator (backend implementation needed for full functionality)
    console.log('Sort by:', field);
}
</script>
@endsection