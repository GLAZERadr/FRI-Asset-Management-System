<!-- resources/views/components/form-input.blade.php -->
@props(['disabled' => false, 'label', 'name', 'type' => 'text', 'value' => ''])

<div class="mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    
    @if ($type === 'textarea')
        <textarea 
            id="{{ $name }}" 
            name="{{ $name }}" 
            {{ $disabled ? 'disabled' : '' }} 
            {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500']) }}
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select 
            id="{{ $name }}" 
            name="{{ $name }}" 
            {{ $disabled ? 'disabled' : '' }} 
            {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500']) }}
        >
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}" 
            {{ $disabled ? 'disabled' : '' }} 
            {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500']) }}
        >
    @endif
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>