@extends('layout')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4">Add Event</h2>
    <form method="POST" action="{{ route('events.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Event Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" value="{{ old('name') }}" required>
            @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Start Time</label>
            <input type="datetime-local" name="start_time" class="w-full border rounded px-3 py-2" value="{{ old('start_time') }}" required>
            @error('start_time')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">End Time</label>
            <input type="datetime-local" name="end_time" class="w-full border rounded px-3 py-2" value="{{ old('end_time') }}" required>
            @error('end_time')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Location</label>
            <input type="text" name="location" class="w-full border rounded px-3 py-2" value="{{ old('location') }}">
            @error('location')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('events.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
        </div>
    </form>
</div>
@endsection