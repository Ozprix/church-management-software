@extends('layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-primary-dark">Members</h1>
    <a href="{{ route('members.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">Add Member</a>
</div>
@if(session('success'))
<div class="mb-4 p-2 bg-success/10 text-success rounded">{{ session('success') }}</div>
@endif
<div class="bg-white rounded shadow p-6 overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Email</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-neutral-dark uppercase">Phone</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $member->name }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $member->email }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $member->phone }}</td>
                <td class="px-4 py-2 whitespace-nowrap flex gap-2">
                    <a href="{{ route('members.edit', $member) }}" class="text-primary hover:underline">Edit</a>
                    <form action="{{ route('members.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete this member?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-error hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-2 text-center text-neutral-dark">No members found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection