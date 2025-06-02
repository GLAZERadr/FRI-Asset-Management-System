<!-- Mobile Quick Menu -->
<div class="bg-white rounded-lg p-4 shadow-sm" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center justify-between w-full text-left">
        <h3 class="text-sm font-semibold text-gray-700">Menu Cepat</h3>
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
    </button>
    
    <div x-show="open" x-transition class="mt-3 space-y-2" style="display: none;">
        @can('show_asset')
        <a href="{{ route('pemantauan.index') }}" class="flex items-center p-2 text-sm text-gray-600 hover:bg-gray-50 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
            </svg>
            Data Aset
        </a>
        @endcan
        
        @can('create_asset')
        <a href="{{ route('pemantauan.create') }}" class="flex items-center p-2 text-sm text-gray-600 hover:bg-gray-50 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tambah Aset
        </a>
        @endcan
        
        @can('create_maintenance_request')
        <a href="{{ route('pengajuan.create') }}" class="flex items-center p-2 text-sm text-gray-600 hover:bg-gray-50 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Buat Pengajuan
        </a>
        @endcan
        
        @can('show_maintenance_request')
        <a href="{{ route('pengajuan.daftar') }}" class="flex items-center p-2 text-sm text-gray-600 hover:bg-gray-50 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Daftar Pengajuan
        </a>
        @endcan
    </div>
</div>