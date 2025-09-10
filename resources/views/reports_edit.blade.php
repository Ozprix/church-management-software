@extends('layout')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4">Edit Report</h2>
    <form method="POST" action="{{ route('reports.update', $report) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" value="{{ old('title', $report->title) }}" required>
            @error('title')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Type</label>
            <input type="text" name="type" class="w-full border rounded px-3 py-2" value="{{ old('type', $report->type) }}" required>
            @error('type')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
        </div>
    </form>
</div>
@endsection