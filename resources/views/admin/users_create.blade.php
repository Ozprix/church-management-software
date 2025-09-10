@extends('admin.dashboard')

@section('admin-content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4 text-primary-dark">Add User</h2>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-neutral-dark mb-1">Name</label>
            <input type="text" name="name" class="w-full border border-neutral-dark rounded px-3 py-2" value="{{ old('name') }}" required>
            @error('name')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-neutral-dark mb-1">Email</label>
            <input type="email" name="email" class="w-full border border-neutral-dark rounded px-3 py-2" value="{{ old('email') }}" required>
            @error('email')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-neutral-dark mb-1">Password</label>
            <input type="password" name="password" class="w-full border border-neutral-dark rounded px-3 py-2" required>
            @error('password')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-neutral text-neutral-dark rounded hover:bg-neutral-light">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark">Save</button>
        </div>
    </form>
</div>
@endsection