<!-- resources/views/components/button.blade.php -->
@props(['color' => 'blue', 'type' => 'button', 'size' => 'md'])

@php
$colors = [
    'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    'purple' => 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500',
    'gray' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500',
];

$sizes = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$colorClasses = $colors[$color] ?? $colors['blue'];
$sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "{$colorClasses} {$sizeClasses} text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 {$colorClasses}"]) }}
>
    {{ $slot }}
</button>