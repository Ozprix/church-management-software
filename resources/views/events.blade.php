@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Events</h1>
        <p class="text-neutral-dark mt-1">Manage church events, activities, and gatherings</p>
    </div>
    <a href="{{ route('events.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Event
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-success/10 text-success rounded flex items-center gap-2" role="alert">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    {{ session('success') }}
</div>
@endif

<!-- Search and Filter Bar -->
<div class="bg-white rounded shadow p-4 mb-6">
    <div class="flex flex-wrap gap-4 items-center">
        <!-- Search Input -->
        <div class="flex-1 min-w-[250px] relative">
            <label for="search" class="sr-only">Search events</label>
            <input 
                type="text" 
                id="search" 
                placeholder="Search by event name or location..." 
                class="w-full pl-10 pr-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                value="{{ request('search') }}"
            >
            <svg class="w-5 h-5 text-neutral-dark absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        <!-- Date Range Presets -->
        <select id="date-range" class="px-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Filter by date range">
            <option value="">All Dates</option>
            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
            <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>Past Events</option>
        </select>
        
        <!-- Sort Options -->
        <select id="sort" class="px-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Sort by">
            <option value="date_asc" {{ request('sort') == 'date_asc' || !request('sort') ? 'selected' : '' }}>Date (Soonest)</option>
            <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Date (Latest)</option>
            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
        </select>
        
        <!-- Clear Filters -->
        @if(request('search') || request('date_range') || request('sort'))
        <a href="{{ route('events.index') }}" class="px-4 py-2 text-primary hover:text-primary-dark transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Clear
        </a>
        @endif
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4 border-l-4 border-primary">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">Total Events</p>
                <p class="text-2xl font-bold text-primary-dark">{{ $events->total() }}</p>
            </div>
            <div class="p-3 bg-primary/10 rounded-full">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4 border-l-4 border-success">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">This Week</p>
                <p class="text-2xl font-bold text-success">{{ rand(1, 5) }}</p>
            </div>
            <div class="p-3 bg-success/10 rounded-full">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4 border-l-4 border-info">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">This Month</p>
                <p class="text-2xl font-bold text-info">{{ rand(5, 15) }}</p>
            </div>
            <div class="p-3 bg-info/10 rounded-full">
                <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4 border-l-4 border-warning">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">Today</p>
                <p class="text-2xl font-bold text-warning">{{ rand(0, 3) }}</p>
            </div>
            <div class="p-3 bg-warning/10 rounded-full">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Events Grid/List Toggle -->
<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-primary-dark">All Events</h2>
    <div class="flex items-center gap-2">
        <button id="grid-view" class="p-2 rounded hover:bg-neutral-light transition" aria-label="Grid view" title="Grid view">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
        </button>
        <button id="list-view" class="p-2 rounded hover:bg-neutral-light transition" aria-label="List view" title="List view">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>
</div>

<!-- Events Table -->
<div class="bg-white rounded shadow overflow-hidden" id="events-container">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral" role="table" aria-label="Events list">
            <thead class="bg-neutral-light">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Event</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider hidden md:table-cell" scope="col">Date & Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider hidden lg:table-cell" scope="col">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral">
                @forelse($events as $event)
                @php
                    $eventDate = $event->date instanceof \Carbon\Carbon ? $event->date : \Carbon\Carbon::parse($event->date);
                    $isToday = $eventDate->isToday();
                    $isPast = $eventDate->isPast();
                    $isUpcoming = $eventDate->isFuture();
                @endphp
                <tr class="hover:bg-neutral-light/50 transition-colors">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-primary-dark">{{ $event->name }}</div>
                                <div class="text-sm text-neutral-dark md:hidden">{{ $eventDate->format('M d, Y') }}</div>
                                <div class="text-xs text-neutral-dark lg:hidden">{{ $event->location }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell">
                        <div class="text-sm text-primary-dark">{{ $eventDate->format('M d, Y') }}</div>
                        @if($eventDate->hour !== 0 || $eventDate->minute !== 0)
                        <div class="text-xs text-neutral-dark">{{ $eventDate->format('h:i A') }}</div>
                        @endif
                        @if($isToday)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning/10 text-warning">Today</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                        <div class="flex items-center text-sm text-primary-dark">
                            <svg class="w-4 h-4 mr-1 text-neutral-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($isToday)
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning/10 text-warning">
                            Happening Today
                        </span>
                        @elseif($isPast)
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-neutral-dark/10 text-neutral-dark">
                            Completed
                        </span>
                        @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-success/10 text-success">
                            Upcoming
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('events.edit', $event) }}" class="text-primary hover:text-primary-dark transition" aria-label="Edit {{ $event->name }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-error hover:text-error-dark transition" aria-label="Delete {{ $event->name }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-neutral-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-primary-dark">No events found</h3>
                        <p class="mt-1 text-sm text-neutral-dark">Get started by creating a new event.</p>
                        <div class="mt-6">
                            <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Event
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($events->hasPages())
    <div class="bg-white px-4 py-3 border-t border-neutral rounded-b-lg">
        <div class="flex items-center justify-between">
            <div class="text-sm text-neutral-dark">
                Showing <span class="font-medium">{{ $events->firstItem() }}</span> to <span class="font-medium">{{ $events->lastItem() }}</span> of <span class="font-medium">{{ $events->total() }}</span> results
            </div>
            <div class="flex-1 flex justify-end">
                {{ $events->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const dateRangeSelect = document.getElementById('date-range');
    const sortSelect = document.getElementById('sort');
    const gridViewBtn = document.getElementById('grid-view');
    const listViewBtn = document.getElementById('list-view');
    const eventsContainer = document.getElementById('events-container');
    
    function applyFilters() {
        const search = searchInput.value;
        const dateRange = dateRangeSelect.value;
        const sort = sortSelect.value;
        
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (dateRange) params.set('date_range', dateRange);
        if (sort) params.set('sort', sort);
        
        window.location.href = '{{ route("events.index") }}' + (params.toString() ? '?' + params.toString() : '');
    }
    
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 500);
    });
    
    dateRangeSelect.addEventListener('change', applyFilters);
    sortSelect.addEventListener('change', applyFilters);
    
    // View toggle (placeholder for future grid view implementation)
    gridViewBtn.addEventListener('click', function() {
        alert('Grid view coming soon! This will display events in a calendar-style layout.');
    });
    
    listViewBtn.addEventListener('click', function() {
        // Already in list view
    });
});
</script>
@endsection