@extends('layouts.mobile')

@section('content')
<div class="space-y-4">
    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Data Kelayakan Aset -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Data Kelayakan Aset</h3>
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            
            <!-- Chart Container -->
            <div class="relative h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg flex items-center justify-center mb-3">
                <canvas id="kelayakanChart" width="80" height="80" class="max-h-16"></canvas>
            </div>
            
            <!-- Legend -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Layak</span>
                    </div>
                    <span class="font-medium text-gray-700">{{ $assetStats['layak'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                        <span class="text-gray-600">Tidak Layak</span>
                    </div>
                    <span class="font-medium text-gray-700">{{ $assetStats['tidak_layak'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Data Kerusakan Aset -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Data Kerusakan Aset</h3>
                <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            
            <!-- Chart Container -->
            <div class="relative h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg flex items-center justify-center mb-3">
                <canvas id="kerusakanChart" width="80" height="80" class="max-h-16"></canvas>
            </div>
            
            <!-- Legend -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Ringan</span>
                    </div>
                    <span class="font-medium text-gray-700">{{ $damageStats['ringan'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Sedang</span>
                    </div>
                    <span class="font-medium text-gray-700">{{ $damageStats['sedang'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-gray-600">Berat</span>
                    </div>
                    <span class="font-medium text-gray-700">{{ $damageStats['berat'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Fitur Aplikasi</h3>
        
        <div class="grid grid-cols-2 gap-3">
            @can('show_asset')
            <a href="{{ route('pemantauan.index') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-200 transform hover:scale-105">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-600 transition-colors shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-700 text-center">Data Aset</span>
            </a>
            @endcan

            @can('show_maintenance_request')
            <a href="{{ route('pengajuan.daftar') }}" class="group flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-200 transform hover:scale-105">
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-600 transition-colors shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-700 text-center">Laporan Monitoring</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Kalender Perbaikan</h3>
            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012-2h4a1 1 0 012 2v4M8 7h8M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3" />
                </svg>
            </div>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($upcomingMaintenance as $index => $maintenance)
                @php
                    $colors = ['emerald', 'blue', 'amber', 'purple', 'pink'];
                    $color = $colors[$index % count($colors)];
                    $date = $maintenance->tanggal_pengajuan;
                @endphp
                
                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-{{ $color }}-400">
                    <div class="text-xs font-medium text-gray-500 mb-1">
                        {{ $date->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="text-sm font-medium text-gray-700 mb-1">
                        {{ Str::limit($maintenance->asset->nama_asset ?? 'N/A', 25) }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ $maintenance->asset_id }}</span>
                        <span class="px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 text-xs rounded-full font-medium">
                            {{ $maintenance->status }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012-2h4a1 1 0 012 2v4M8 7h8M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">Tidak ada jadwal perbaikan</p>
                    <p class="text-xs text-gray-400">Semua aset dalam kondisi baik</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Aktivitas Terbaru</h3>
            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($recentActivities as $activity)
                @php
                    $colorClass = match($activity['color']) {
                        'green' => 'bg-green-500',
                        'yellow' => 'bg-yellow-500',
                        'blue' => 'bg-blue-500',
                        'red' => 'bg-red-500',
                        default => 'bg-gray-500'
                    };
                    
                    $bgClass = match($activity['color']) {
                        'green' => 'bg-green-50 border-green-200',
                        'yellow' => 'bg-yellow-50 border-yellow-200',
                        'blue' => 'bg-blue-50 border-blue-200',
                        'red' => 'bg-red-50 border-red-200',
                        default => 'bg-gray-50 border-gray-200'
                    };
                @endphp
                
                <div class="flex items-start space-x-3 p-3 {{ $bgClass }} rounded-lg border">
                    <div class="w-2 h-2 {{ $colorClass }} rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-gray-700 font-medium">{{ $activity['message'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $activity['time']->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">Tidak ada aktivitas terbaru</p>
                    <p class="text-xs text-gray-400">Aktivitas akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Chart Data Script -->
<script>
// Store data from PHP in JavaScript variables
window.assetData = {
    layak: {{ $assetStats['layak'] ?? 0 }},
    tidakLayak: {{ $assetStats['tidak_layak'] ?? 0 }}
};

window.damageData = {
    ringan: {{ $damageStats['ringan'] ?? 0 }},
    sedang: {{ $damageStats['sedang'] ?? 0 }},
    berat: {{ $damageStats['berat'] ?? 0 }}
};

document.addEventListener('DOMContentLoaded', function() {
    drawKelayakanChart();
    drawKerusakanChart();
});

function drawKelayakanChart() {
    const canvas = document.getElementById('kelayakanChart');
    const ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    const layak = window.assetData.layak;
    const tidakLayak = window.assetData.tidakLayak;
    const total = layak + tidakLayak;
    
    if (total === 0) {
        // Draw empty state
        ctx.fillStyle = '#E5E7EB';
        ctx.beginPath();
        ctx.arc(40, 40, 25, 0, 2 * Math.PI);
        ctx.fill();
        
        ctx.fillStyle = '#6B7280';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No Data', 40, 45);
        return;
    }
    
    // Draw simple pie chart
    const centerX = 40;
    const centerY = 40;
    const radius = 25;
    
    let currentAngle = 0;
    
    // Draw "Layak" slice (green)
    if (layak > 0) {
        const layakAngle = (layak / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + layakAngle);
        ctx.lineTo(centerX, centerY);
        ctx.fillStyle = '#10B981';
        ctx.fill();
        currentAngle += layakAngle;
    }
    
    // Draw "Tidak Layak" slice (yellow)
    if (tidakLayak > 0) {
        const tidakLayakAngle = (tidakLayak / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + tidakLayakAngle);
        ctx.lineTo(centerX, centerY);
        ctx.fillStyle = '#F59E0B';
        ctx.fill();
    }
}

function drawKerusakanChart() {
    const canvas = document.getElementById('kerusakanChart');
    const ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    const ringan = window.damageData.ringan;
    const sedang = window.damageData.sedang;
    const berat = window.damageData.berat;
    const total = ringan + sedang + berat;
    
    if (total === 0) {
        // Draw empty state
        ctx.fillStyle = '#E5E7EB';
        ctx.beginPath();
        ctx.arc(40, 40, 25, 0, 2 * Math.PI);
        ctx.fill();
        
        ctx.fillStyle = '#6B7280';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No Data', 40, 45);
        return;
    }
    
    // Draw simple pie chart
    const centerX = 40;
    const centerY = 40;
    const radius = 25;
    
    let currentAngle = 0;
    
    // Draw "Ringan" slice (blue)
    if (ringan > 0) {
        const ringanAngle = (ringan / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + ringanAngle);
        ctx.lineTo(centerX, centerY);
        ctx.fillStyle = '#3B82F6';
        ctx.fill();
        currentAngle += ringanAngle;
    }
    
    // Draw "Sedang" slice (yellow)
    if (sedang > 0) {
        const sedangAngle = (sedang / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sedangAngle);
        ctx.lineTo(centerX, centerY);
        ctx.fillStyle = '#F59E0B';
        ctx.fill();
        currentAngle += sedangAngle;
    }
    
    // Draw "Berat" slice (red)
    if (berat > 0) {
        const beratAngle = (berat / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + beratAngle);
        ctx.lineTo(centerX, centerY);
        ctx.fillStyle = '#EF4444';
        ctx.fill();
    }
}
</script>
@endsection