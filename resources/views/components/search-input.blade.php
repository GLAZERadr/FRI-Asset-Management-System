<!-- resources/views/components/search-input.blade.php -->
<div class="flex items-center">
    <div class="relative flex-grow">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input 
            {{ $attributes->merge([
                'type' => 'text', 
                'class' => 'focus:ring-green-500 focus:border-green-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md'
            ]) }}
        />
    </div>
    {{ $slot ?? '' }}
</div>