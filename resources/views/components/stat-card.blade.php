@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'trend' => null,
    'trendLabel' => null,
    'href' => null,
])

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                @if($icon)
                    {!! $icon !!}
                @else
                    <div class="rounded-md bg-indigo-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd>
                        <div class="text-lg font-medium text-gray-900">
                            {{ $value }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    
    @if($trend !== null)
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <span class="{{ $trend >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                    {{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
                </span>
                <span class="text-gray-500"> {{ $trendLabel ?? 'from last period' }}</span>
            </div>
        </div>
    @endif
    
    @if($href)
        <div class="bg-gray-50 px-5 py-3">
            <a href="{{ $href }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                View all
                <span aria-hidden="true">→</span>
            </a>
        </div>
    @endif
</div>
