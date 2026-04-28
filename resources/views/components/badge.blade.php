@props([
    'text' => '',
    'variant' => 'primary', // primary, success, warning, danger, info, neutral
    'size' => 'md', // sm, md, lg
])

@php
$variants = [
    'primary' => 'bg-indigo-100 text-indigo-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
    'neutral' => 'bg-gray-100 text-gray-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-sm',
    'lg' => 'px-3 py-1 text-base',
];

$classes = $variants[$variant] . ' ' . $sizes[$size] . ' font-medium inline-flex items-center rounded-full';
@endphp

<span class="{{ $classes }}">
    {{ $slot }}
</span>
