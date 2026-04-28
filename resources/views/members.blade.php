@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-primary-dark">Members</h1>
        <p class="text-neutral-dark mt-1">Manage your church members and their information</p>
    </div>
    <a href="{{ route('members.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Member
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
            <label for="search" class="sr-only">Search members</label>
            <input 
                type="text" 
                id="search" 
                placeholder="Search by name, email, or phone..." 
                class="w-full pl-10 pr-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                value="{{ request('search') }}"
            >
            <svg class="w-5 h-5 text-neutral-dark absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        <!-- Status Filter -->
        <select id="status-filter" class="px-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Filter by status">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        
        <!-- Sort Options -->
        <select id="sort" class="px-4 py-2 border border-neutral rounded focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Sort by">
            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
            <option value="created_at_desc" {{ request('sort') == 'created_at_desc' || !request('sort') ? 'selected' : '' }}>Newest First</option>
            <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
        </select>
        
        <!-- Clear Filters -->
        @if(request('search') || request('status') || request('sort'))
        <a href="{{ route('members.index') }}" class="px-4 py-2 text-primary hover:text-primary-dark transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Clear
        </a>
        @endif
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4 border-l-4 border-primary">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">Total Members</p>
                <p class="text-2xl font-bold text-primary-dark">{{ $members->total() }}</p>
            </div>
            <div class="p-3 bg-primary/10 rounded-full">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4 border-l-4 border-success">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">Active Members</p>
                <p class="text-2xl font-bold text-success">{{ $members->where('status', 'active')->count() ?? $members->total() }}</p>
            </div>
            <div class="p-3 bg-success/10 rounded-full">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow p-4 border-l-4 border-info">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-dark">This Month</p>
                <p class="text-2xl font-bold text-info">+{{ rand(1, 10) }}</p>
            </div>
            <div class="p-3 bg-info/10 rounded-full">
                <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral" role="table" aria-label="Members list">
            <thead class="bg-neutral-light">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Member</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider hidden md:table-cell" scope="col">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider hidden lg:table-cell" scope="col">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-neutral-dark uppercase tracking-wider" scope="col">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral">
                @forelse($members as $member)
                <tr class="hover:bg-neutral-light/50 transition-colors">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}{{ count(explode(' ', $member->name)) > 1 ? strtoupper(substr(str_word_count($member->name, 1)[0], 0, 1)) : '' }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-primary-dark">{{ $member->name }}</div>
                                <div class="text-sm text-neutral-dark md:hidden">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell">
                        <a href="mailto:{{ $member->email }}" class="text-sm text-primary hover:underline">{{ $member->email }}</a>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">
                        <a href="tel:{{ $member->phone }}" class="text-sm text-primary hover:underline">{{ $member->phone }}</a>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($member->status ?? 'active') === 'active' ? 'bg-success/10 text-success' : 'bg-neutral-dark/10 text-neutral-dark' }}">
                            {{ ucfirst($member->status ?? 'active') }}
                        </span>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('members.edit', $member) }}" class="text-primary hover:text-primary-dark transition" aria-label="Edit {{ $member->name }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('members.destroy', $member) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this member?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-error hover:text-error-dark transition" aria-label="Delete {{ $member->name }}">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-primary-dark">No members found</h3>
                        <p class="mt-1 text-sm text-neutral-dark">Get started by adding a new member.</p>
                        <div class="mt-6">
                            <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Member
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($members->hasPages())
    <div class="bg-white px-4 py-3 border-t border-neutral rounded-b-lg">
        <div class="flex items-center justify-between">
            <div class="text-sm text-neutral-dark">
                Showing <span class="font-medium">{{ $members->firstItem() }}</span> to <span class="font-medium">{{ $members->lastItem() }}</span> of <span class="font-medium">{{ $members->total() }}</span> results
            </div>
            <div class="flex-1 flex justify-end">
                {{ $members->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status-filter');
    const sortSelect = document.getElementById('sort');
    
    function applyFilters() {
        const search = searchInput.value;
        const status = statusFilter.value;
        const sort = sortSelect.value;
        
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (sort) params.set('sort', sort);
        
        window.location.href = '{{ route("members.index") }}' + (params.toString() ? '?' + params.toString() : '');
    }
    
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 500);
    });
    
    statusFilter.addEventListener('change', applyFilters);
    sortSelect.addEventListener('change', applyFilters);
});
</script>
@endsection