@props(['id', 'title', 'submitText' => 'Delete', 'submitColor' => 'red'])

<div id="{{ $id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center" x-data>
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.away="closeModal('{{ $id }}')">
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
            <div class="mt-2 px-7 py-3">
                {{ $message }}
            </div>
            <div class="flex justify-end mt-4 space-x-3">
                <button type="button" onclick="closeModal('{{ $id }}')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50">
                    Cancel
                </button>
                
                <button type="button" id="{{ $id }}-confirm-button" 
                        class="px-4 py-2 bg-{{ $submitColor }}-600 text-white rounded-md hover:bg-{{ $submitColor }}-700 focus:outline-none focus:ring-2 focus:ring-{{ $submitColor }}-400 focus:ring-opacity-50">
                    {{ $submitText }}
                </button>
            </div>
        </div>
    </div>
</div>