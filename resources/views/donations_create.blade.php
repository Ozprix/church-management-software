@extends('layout')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-primary-dark">Add Donation</h2>
            <p class="text-sm text-neutral-dark mt-1">Record a new donation from a member</p>
        </div>
        <a href="{{ route('donations.index') }}" class="text-primary hover:text-primary-dark transition flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancel
        </a>
    </div>
    
    <form method="POST" action="{{ route('donations.store') }}" class="space-y-5">
        @csrf
        
        <!-- Member Selection with Search -->
        <div>
            <label for="member_id" class="block text-sm font-medium text-neutral-dark mb-2">
                Member <span class="text-error">*</span>
            </label>
            <div class="relative">
                <select name="member_id" id="member_id" class="w-full border border-neutral rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent appearance-none bg-white" required>
                    <option value="">Select a member</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}" @if(old('member_id')==$member->id) selected @endif>{{ $member->name }} @if($member->email)- {{ $member->email }}@endif</option>
                    @endforeach
                </select>
                <svg class="w-5 h-5 text-neutral-dark absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            @error('member_id')<div class="text-error text-xs mt-1 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $message }}
            </div>@enderror
        </div>
        
        <!-- Amount Field -->
        <div>
            <label for="amount" class="block text-sm font-medium text-neutral-dark mb-2">
                Amount (₦) <span class="text-error">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-neutral-dark font-semibold">₦</span>
                <input type="number" 
                       name="amount" 
                       id="amount" 
                       class="w-full border border-neutral rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                       value="{{ old('amount') }}" 
                       placeholder="0.00"
                       required 
                       min="1"
                       step="0.01">
            </div>
            @error('amount')<div class="text-error text-xs mt-1 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $message }}
            </div>@enderror
        </div>
        
        <!-- Amount Presets -->
        <div>
            <label class="block text-sm font-medium text-neutral-dark mb-2">Quick Amounts</label>
            <div class="grid grid-cols-4 gap-2">
                <button type="button" class="amount-preset px-4 py-2 border border-neutral rounded-lg hover:bg-primary hover:text-white hover:border-primary transition text-sm" data-amount="1000">₦1,000</button>
                <button type="button" class="amount-preset px-4 py-2 border border-neutral rounded-lg hover:bg-primary hover:text-white hover:border-primary transition text-sm" data-amount="5000">₦5,000</button>
                <button type="button" class="amount-preset px-4 py-2 border border-neutral rounded-lg hover:bg-primary hover:text-white hover:border-primary transition text-sm" data-amount="10000">₦10,000</button>
                <button type="button" class="amount-preset px-4 py-2 border border-neutral rounded-lg hover:bg-primary hover:text-white hover:border-primary transition text-sm" data-amount="50000">₦50,000</button>
            </div>
        </div>
        
        <!-- Donation Category -->
        <div>
            <label for="category" class="block text-sm font-medium text-neutral-dark mb-2">
                Category
            </label>
            <div class="relative">
                <select name="category" id="category" class="w-full border border-neutral rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent appearance-none bg-white">
                    <option value="tithe" {{ old('category')=='tithe' ? 'selected' : '' }}>Tithe</option>
                    <option value="offering" {{ old('category')=='offering' ? 'selected' : '' }}>Offering</option>
                    <option value="building_fund" {{ old('category')=='building_fund' ? 'selected' : '' }}>Building Fund</option>
                    <option value="mission" {{ old('category')=='mission' ? 'selected' : '' }}>Mission</option>
                    <option value="youth" {{ old('category')=='youth' ? 'selected' : '' }}>Youth Ministry</option>
                    <option value="children" {{ old('category')=='children' ? 'selected' : '' }}>Children's Church</option>
                    <option value="other" {{ old('category')=='other' ? 'selected' : '' }}>Other</option>
                </select>
                <svg class="w-5 h-5 text-neutral-dark absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            @error('category')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        
        <!-- Payment Method -->
        <div>
            <label class="block text-sm font-medium text-neutral-dark mb-2">Payment Method</label>
            <div class="grid grid-cols-3 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="cash" {{ old('payment_method', 'cash')=='cash' ? 'checked' : '' }} class="peer sr-only">
                    <div class="border border-neutral rounded-lg p-3 text-center peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:bg-neutral-light transition">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="text-xs">Cash</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method')=='bank_transfer' ? 'checked' : '' }} class="peer sr-only">
                    <div class="border border-neutral rounded-lg p-3 text-center peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:bg-neutral-light transition">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                        </svg>
                        <span class="text-xs">Bank Transfer</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="card" {{ old('payment_method')=='card' ? 'checked' : '' }} class="peer sr-only">
                    <div class="border border-neutral rounded-lg p-3 text-center peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:bg-neutral-light transition">
                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="text-xs">Card</span>
                    </div>
                </label>
            </div>
            @error('payment_method')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        
        <!-- Date Field -->
        <div>
            <label for="date" class="block text-sm font-medium text-neutral-dark mb-2">
                Donation Date
            </label>
            <input type="date" 
                   name="date" 
                   id="date" 
                   class="w-full border border-neutral rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                   value="{{ old('date', date('Y-m-d')) }}">
            @error('date')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        
        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-neutral-dark mb-2">Notes (Optional)</label>
            <textarea name="notes" 
                      id="notes" 
                      rows="3" 
                      class="w-full border border-neutral rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none" 
                      placeholder="Any additional information about this donation...">{{ old('notes') }}</textarea>
            @error('notes')<div class="text-error text-xs mt-1">{{ $message }}</div>@enderror
        </div>
        
        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('donations.index') }}" class="px-6 py-2.5 border border-neutral text-neutral-dark rounded-lg hover:bg-neutral-light transition font-medium">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Donation
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Amount preset buttons
    const presetButtons = document.querySelectorAll('.amount-preset');
    const amountInput = document.getElementById('amount');
    
    presetButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            amountInput.value = amount;
            amountInput.focus();
            
            // Visual feedback
            presetButtons.forEach(btn => btn.classList.remove('bg-primary', 'text-white', 'border-primary'));
            this.classList.add('bg-primary', 'text-white', 'border-primary');
        });
    });
    
    // Remove highlight when user types
    amountInput.addEventListener('input', function() {
        presetButtons.forEach(btn => btn.classList.remove('bg-primary', 'text-white', 'border-primary'));
    });
});
</script>
@endsection