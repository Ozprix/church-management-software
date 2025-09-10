@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-primary-dark">Reports</h1>
    <a href="{{ route('reports.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">Add Report</a>
</div>
<form method="GET" action="" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Type</label>
        <select name="type" class="border rounded px-2 py-1">
            <option value="">All</option>
            @foreach($types as $type)
            <option value="{{ $type }}" @if(request('type')==$type) selected @endif>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Start Date</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="border rounded px-2 py-1">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">End Date</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="border rounded px-2 py-1">
    </div>
    <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded">Filter</button>
</form>
@if(session('success'))
<div class="mb-4 p-2 bg-success/10 text-success rounded">{{ session('success') }}</div>
@endif
<div class="bg-white rounded shadow p-6 overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Title</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Date</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $report->title }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $report->type }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $report->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-2 whitespace-nowrap flex gap-2">
                    <a href="{{ route('reports.edit', $report) }}" class="text-primary hover:underline">Edit</a>
                    <form action="{{ route('reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Delete this report?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-error hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-2 text-center text-neutral-dark">No reports found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection