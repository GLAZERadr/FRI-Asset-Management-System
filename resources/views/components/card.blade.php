<!-- resources/views/components/card.blade.php -->
@props(['title'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md overflow-hidden']) }}>
    @isset($title)
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-medium text-gray-900">{{ $title }}</h2>
    </div>
    @endisset
    
    <div class="p-6">
        {{ $slot }}
    </div>
    
    @isset($footer)
    <div class="px-6 py-4 bg-gray-50 border-t">
        {{ $footer }}
    </div>
    @endisset
</div>