<!-- resources/views/dashboard/dashboard.blade.php -->
@extends('layouts.app')
@section('header', 'Dashboard')
@section('content')
<div class="container mx-auto">
    <!-- Date Filter with working form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('dashboard') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Role-specific Statistics Cards -->
    @if(auth()->user()->hasRole('staff_logistik'))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Reports Need Verification -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Laporan Perlu Verifikasi
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['reports_need_verification'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Need Verification Today -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Perlu Verifikasi Hari Ini
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['reports_need_verification_today'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Completed -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Perbaikan Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['completed'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total In Progress -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Sedang Dikerjakan
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['in_progress'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif(auth()->user()->hasRole('staff_laboratorium'))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Assets Monitored Total -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Aset Dimonitor
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['assets_monitored_total'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets Monitored Today -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Dimonitor Hari Ini
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['assets_monitored_today'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Completed -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Perbaikan Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['completed'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total In Progress -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Sedang Dikerjakan
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['in_progress'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Default Statistics for Other Roles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Completed -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Selesai
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['completed'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Dikerjakan
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['in_progress'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Received -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Diterima
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['received'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Expenditure -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Pengeluaran
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    Rp {{ number_format($stats['total_expenditure'] ?? 0, 0, ',', '.') }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Section - Show for All Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi Status Pengajuan</h3>
            <div class="w-full h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Monthly Expenditure Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Pengeluaran per Bulan</h3>
            <div class="w-full h-64">
                <canvas id="expenditureChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Requests Table - Show for All Users -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                @if(auth()->user()->hasRole('staff_logistik'))
                    Laporan Monitoring Terbaru
                @elseif(auth()->user()->hasRole('staff_laboratorium'))
                    Aktivitas Monitoring Terbaru
                @else
                    Daftar Perbaikan Aset Terbaru
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if(auth()->user()->hasRole('staff_logistik'))
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Laporan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode Ruangan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Pelapor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status Verifikasi
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Laporan
                            </th>
                        @elseif(auth()->user()->hasRole('staff_laboratorium'))
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Laporan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode Ruangan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Aset
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                        @else
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Perbaikan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Aset
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Pengajuan
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($recentRequests ?? [] as $request)
                        <tr class="hover:bg-gray-50">
                            @if(auth()->user()->hasRole('staff_logistik'))
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $request->id_laporan ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->kode_ruangan ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->nama_pelapor ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($request->validated)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Terverifikasi
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                            Perlu Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->tanggal_laporan ? $request->tanggal_laporan->format('d/m/Y') : '-' }}
                                </td>
                            @elseif(auth()->user()->hasRole('staff_laboratorium'))
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $request->id_laporan ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->kode_ruangan ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ is_array($request->monitoring_data) ? count($request->monitoring_data) : 0 }} aset
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($request->validated)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Monitoring
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->tanggal_laporan ? $request->tanggal_laporan->format('d/m/Y') : '-' }}
                                </td>
                            @else
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $request->maintenance_id ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->asset->nama_asset ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->asset->lokasi ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'Diterima' => 'bg-blue-100 text-blue-800',
                                            'Dikerjakan' => 'bg-yellow-100 text-yellow-800',
                                            'Selesai' => 'bg-green-100 text-green-800',
                                            'Ditolak' => 'bg-red-100 text-red-800'
                                        ];
                                        $class = $statusClasses[$request->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                        {{ $request->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->tanggal_pengajuan ? $request->tanggal_pengajuan->format('d/m/Y') : '-' }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-500 text-lg">Tidak Ada Data</p>
                                    <p class="text-gray-400 text-sm mt-1">
                                        @if($role === 'staff_logistik')
                                            Belum ada laporan monitoring yang perlu diverifikasi.
                                        @elseif($role === 'staff_laboratorium')
                                            Belum ada aktivitas monitoring yang tercatat.
                                        @elseif($role === 'kaur_laboratorium')
                                            Tidak ada pengajuan yang perlu diverifikasi.
                                        @elseif($role === 'kaur_keuangan_logistik_sdm')
                                            Tidak ada pengajuan yang sedang dikerjakan.
                                        @elseif($role === 'wakil_dekan_2')
                                            Tidak ada pengajuan yang perlu persetujuan.
                                        @else
                                            Belum ada data pengajuan.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only initialize charts if the chart elements exist
        const statusCtx = document.getElementById('statusChart');
        const expenditureCtx = document.getElementById('expenditureChart');
        
        if (statusCtx) {
            // Status Distribution Chart
            const statusChart = new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Selesai', 'Dikerjakan', 'Diterima'],
                    datasets: [{
                        data: [{{ $stats['completed'] ?? 0 }}, {{ $stats['in_progress'] ?? 0 }}, {{ $stats['received'] ?? 0 }}],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.7)',  // green
                            'rgba(234, 179, 8, 0.7)',  // yellow  
                            'rgba(59, 130, 246, 0.7)'  // blue
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(234, 179, 8, 1)',
                            'rgba(59, 130, 246, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        if (expenditureCtx) {
            // Fetch monthly expenditure data for chart
            fetch('{{ route("dashboard.expenditure") }}')
                .then(response => response.json())
                .then(data => {
                    const expenditureChart = new Chart(expenditureCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Pengeluaran (Rp)',
                                data: data.values,
                                backgroundColor: 'rgba(139, 92, 246, 0.2)',
                                borderColor: 'rgba(139, 92, 246, 1)',
                                borderWidth: 2,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching expenditure data:', error);
                });
        }
    });
</script>
@endsection