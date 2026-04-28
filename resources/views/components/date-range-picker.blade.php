@props([
    'start' => null,
    'end' => null,
    'presets' => true,
    'wireModelStart' => null,
    'wireModelEnd' => null,
])

<div class="space-y-2">
    @if($presets)
        <div class="flex flex-wrap gap-2">
            <button type="button" 
                    wire:click="setDateRange('today')"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                Today
            </button>
            <button type="button" 
                    wire:click="setDateRange('week')"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                This Week
            </button>
            <button type="button" 
                    wire:click="setDateRange('month')"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                This Month
            </button>
            <button type="button" 
                    wire:click="setDateRange('quarter')"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                Quarter
            </button>
            <button type="button" 
                    wire:click="setDateRange('year')"
                    class="px-3 py-1 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                Year
            </button>
        </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            @if($wireModelStart)
                <input type="date" 
                       wire:model="{{ $wireModelStart }}"
                       value="{{ $start }}"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @else
                <input type="date" 
                       value="{{ $start }}"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @endif
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            @if($wireModelEnd)
                <input type="date" 
                       wire:model="{{ $wireModelEnd }}"
                       value="{{ $end }}"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @else
                <input type="date" 
                       value="{{ $end }}"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @endif
        </div>
    </div>
</div>
