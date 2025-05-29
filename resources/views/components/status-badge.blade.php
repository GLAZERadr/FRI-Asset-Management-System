<!-- resources/views/components/status-badge.blade.php -->
@props(['status'])

@php
$color = match ($status) {
    'Diterima' => 'blue',
    'Dikerjakan' => 'yellow',
    'Selesai' => 'green',
    'Ditolak' => 'red',
    'Ringan' => 'blue',
    'Sedang' => 'yellow',
    'Berat' => 'red',
    default => 'gray',
};
@endphp

<span {{ $attributes->merge(['class' => "px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{$color}-100 text-{$color}-800"]) }}>
    {{ $status }}
</span>