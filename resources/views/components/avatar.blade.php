@props([
    'name' => '',
    'email' => '',
    'size' => 'md', // sm, md, lg
    'class' => '',
])

@php
$sizes = [
    'sm' => 'h-8 w-8 text-sm',
    'md' => 'h-10 w-10 text-base',
    'lg' => 'h-12 w-12 text-lg',
];

$initials = '';
if ($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } elseif (count($parts) === 1) {
        $initials = strtoupper(substr($parts[0], 0, 2));
    }
}

$colors = [
    'bg-indigo-500',
    'bg-green-500',
    'bg-yellow-500',
    'bg-red-500',
    'bg-blue-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-teal-500',
];

$colorIndex = $email ? abs(crc32($email)) % count($colors) : 0;
$bgColor = $colors[$colorIndex];
@endphp

<div class="{{ $sizes[$size] }} {{ $bgColor }} rounded-full flex items-center justify-center text-white font-semibold {{ $class }}" 
     title="{{ $name ?: $email }}">
    @if($initials)
        {{ $initials }}
    @else
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    @endif
</div>
