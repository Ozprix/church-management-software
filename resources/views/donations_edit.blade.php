@extends('layout')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4 text-primary-dark">Edit Donation</h2>
    <form method="POST" action="{{ route('donations.update', $donation) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-neutral-dark mb-1">Member</label>
            <select name="member_id" class="w-full border border-neutral-dark rounded px-3 py-2" required>
                <option value="">Select Member</option>
                @foreach($members as $member)
                <option value="{{ $member->id }}" @if(old('member_id', $donation->member_id) == $member->id) selected @endif>{{ $member->name }}</option>
                @endforeach
            </select>
            @error('member_id')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-neutral-dark mb-1">Amount (₦)</label>
            <input type="number" name="amount" class="w-full border border-neutral-dark rounded px-3 py-2" value="{{ old('amount', $donation->amount) }}" required min="1">
            @error('amount')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('donations.index') }}" class="px-4 py-2 bg-neutral text-neutral-dark rounded hover:bg-neutral-light">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark">Update</button>
        </div>
    </form>
</div>
@endsection