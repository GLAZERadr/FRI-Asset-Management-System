<!-- resources/views/components/table.blade.php -->
<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
        @isset($thead)
            <thead class="bg-gray-50">
                {{ $thead }}
            </thead>
        @endisset
        
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
        
        @isset($tfoot)
            <tfoot class="bg-gray-50">
                {{ $tfoot }}
            </tfoot>
        @endisset
    </table>
</div>