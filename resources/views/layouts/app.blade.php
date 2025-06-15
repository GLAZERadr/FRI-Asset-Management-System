<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Asset Management System') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            padding: 0 6px;
            font-size: 0.75rem;
            line-height: 1.25rem;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        /* Mobile sidebar overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        
        /* Smooth transitions for sidebar */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        
        /* Hide scrollbar on mobile sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            display: none;
        }
        .sidebar-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Ensure consistent spacing and styling */
        .nav-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.75rem 1rem;
            color: #374151;
            transition: background-color 0.15s ease-in-out;
        }
        
        .nav-item:hover {
            background-color: #f3f4f6;
        }
        
        .nav-item.active {
            background-color: #f3f4f6;
            border-right: 4px solid #10b981;
        }
        
        .nav-item-icon {
            height: 1.25rem;
            width: 1.25rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .nav-item-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="sidebar-overlay lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform sidebar-transition lg:translate-x-0 lg:static lg:inset-0"
             :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
             x-show="sidebarOpen || window.innerWidth >= 1024"
             style="display: none;">
            
            <!-- Logo -->
            <div class="flex items-center justify-between p-4 border-b lg:justify-start">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo fri.png') }}" alt="Logo" class="h-12 lg:h-16">
                    <div class="ml-2 text-green-700 font-bold"></div>
                </div>
                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="p-2 rounded-md text-gray-400 hover:text-gray-600 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-4 h-full overflow-y-auto sidebar-scroll pb-20">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   @click="sidebarOpen = false"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="nav-item-text">Dashboard</span>
                </a>

                <!-- Pemantauan Section -->
                <div class="relative" x-data="{ open: {{ request()->routeIs('pemantauan.*') || request()->routeIs('fix-validation.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="nav-item {{ request()->routeIs('pemantauan.*') || request()->routeIs('fix-validation.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="nav-item-text flex-1 text-left">Pemantauan</span>
                        <svg x-show="!open" class="ml-2 h-4 w-4 flex-shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="open" class="ml-2 h-4 w-4 flex-shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="bg-gray-50" style="display: none;">
                        @can('show_asset')
                            <a href="{{ route('pemantauan.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pemantauan.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Data Aset</span>
                            </a>
                        @endcan
                        @can('show_monitoring_verification_report')
                            <a href="{{ route('pemantauan.monitoring.verify') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pemantauan.monitoring.verify') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Verifikasi Laporan</span>
                            </a>
                        @endcan
                        @can('show_monitoring_report_validation')
                            <a href="{{ route('fix-validation.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('fix-validation.*') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Validasi Laporan</span>
                            </a>
                        @endcan
                        @can('show_monitoring_report')
                            <a href="{{ route('pemantauan.monitoring.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pemantauan.monitoring.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Laporan Pemantauan</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Perbaikan Section -->
                <div class="relative" x-data="{ open: {{ request()->routeIs('perbaikan.*') || request()->routeIs('fix-verification.*')  ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="nav-item {{ request()->routeIs('perbaikan.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="nav-item-text flex-1 text-left">Perbaikan</span>
                        <svg x-show="!open" class="ml-2 h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="open" class="ml-2 h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" class="bg-gray-50" style="display: none;">
                        @can('show_fix_verification_report')
                            <a href="{{ route('fix-verification.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('fix-verification.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Verifikasi Laporan</span>
                            </a>
                        @endcan
                        @can('show_fix_verification_history')
                            <a href="{{ route('fix-verification.history') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('fix-verification.history') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Histori Verifikasi</span>
                            </a>
                        @endcan
                        @can('show_fix_damaged_report_validation')
                            <a href="{{ route('perbaikan.validation.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('perbaikan.validation.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Validasi Laporan Kerusakan</span>
                            </a>
                        @endcan
                        @can('show_fix_damaged_report_validation_history')
                            <a href="{{ route('perbaikan.validation.history') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('perbaikan.validation.history') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Histori Validasi</span>
                            </a>
                        @endcan
                        @can('show_fix_periodic_maintenance')
                            <a href="{{ route('perbaikan.pemeliharaan-berkala.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('perbaikan.pemeliharaan-berkala.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Pemeliharaan Berkala</span>
                            </a>
                        @endcan
                        @can('show_fix_status')
                            <a href="{{ route('perbaikan.status.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('perbaikan.status.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Status Perbaikan</span>
                            </a>
                        @endcan
                        @can('show_fix_periodic_maintenance_report')
                            <a href="{{ route('perbaikan.pemeliharaan-berkala.report') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('perbaikan.pemeliharaan-berkala.report') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Laporan Pemeliharaan Berkala</span>
                            </a>
                        @endcan
                        @can('show_fix_final_report')
                            <a href="#" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('fix-verification.history') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Laporan Akhir Perbaikan Aset</span>
                            </a>
                        @endcan
                        @can('show_fix_report')
                            <a href="#" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('fix-verification.history') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Laporan Perbaikan Aset</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Pengajuan Perbaikan Section -->
                <div class="relative" x-data="{ open: {{ request()->routeIs('pengajuan.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="nav-item {{ request()->routeIs('pengajuan.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="nav-item-text flex-1 text-left">Pengajuan Perbaikan</span>
                        <svg x-show="!open" class="ml-2 h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="open" class="ml-2 h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" class="bg-gray-50" style="display: none;">
                        @can('show_maintenance_request')
                            <a href="{{ route('pengajuan.daftar') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pengajuan.daftar') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Daftar Perbaikan Aset</span>
                            </a>
                        @endcan
                        @can('create_criteria')
                            <a href="{{ route('kriteria.create') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('kriteria.create') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Kriteria</span>
                            </a>
                        @endcan
                        @can('create_maintenance_request')
                            <a href="{{ route('pengajuan.baru') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pengajuan.baru') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Pengajuan</span>
                            </a>
                        @endcan
                        @can('create_payment')
                            <a href="{{ route('pembayaran.index') }}" 
                               @click="sidebarOpen = false"
                               class="flex items-center py-2 px-8 text-sm text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pembayaran.index') ? 'bg-gray-200 text-green-600' : '' }}">
                                <span class="truncate">Pembayaran</span>
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Logout -->
                <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-item w-full text-left rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="nav-item-text">Keluar</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b sticky top-0 z-30">
                <div class="px-4 py-3 flex justify-between items-center">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = true" class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 lg:hidden">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg lg:text-2xl font-semibold ml-2 lg:ml-0 truncate">
                            @yield('header', 'Manajemen Aset')
                        </h1>
                    </div>
                    
                    <div class="flex items-center space-x-2 lg:space-x-4">
                        <!-- User info - hidden on small screens, shown on medium+ -->
                        <div class="hidden md:block relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2">
                                <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div class="text-sm text-left hidden lg:block">
                                    <div class="font-semibold truncate max-w-32">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-32">
                                        {{ ucwords(str_replace('_', ' ', Auth::user()->division ?? '')) }}
                                    </div>
                                </div>
                            </button>
                        </div>
                        
                        <!-- Notification Bell -->
                        <div class="relative" x-data="notificationDropdown()">
                            <button @click="toggle()" class="relative p-2 text-gray-500 hover:text-gray-700 rounded-md hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 lg:h-6 lg:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span x-show="unreadCount > 0" class="notification-badge text-xs" x-text="unreadCount"></span>
                            </button>
                            
                            <!-- Notification Dropdown - responsive positioning -->
                            <div x-show="open" 
                                 @click.away="close()" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 max-w-sm bg-white rounded-lg shadow-lg overflow-hidden z-50"
                                 style="display: none;">
                                
                                <div class="py-2 px-4 bg-gray-50 border-b flex justify-between items-center">
                                    <h3 class="text-sm font-semibold text-gray-700">Notifikasi</h3>
                                    <button @click="markAllAsRead()" x-show="unreadCount > 0" class="text-xs text-blue-600 hover:text-blue-800">
                                        Tandai semua dibaca
                                    </button>
                                </div>
                                
                                <div class="max-h-96 overflow-y-auto">
                                    <template x-if="notifications.length === 0">
                                        <div class="py-8 text-center text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-sm">Tidak ada notifikasi</p>
                                        </div>
                                    </template>
                                    
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markAsRead(notification)" 
                                             class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b"
                                             :class="{'bg-blue-50': !notification.read_at}">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                                         :class="notification.type === 'approval_request' ? 'bg-yellow-100' : 'bg-green-100'">
                                                        <svg x-show="notification.type === 'approval_request'" 
                                                             xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <svg x-show="notification.type === 'approval_result'" 
                                                             xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3 flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="notification.title"></p>
                                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2" x-text="notification.message"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="formatTime(notification.created_at)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-4 lg:p-6">
                @if (session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded mb-4 lg:mb-6">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4 lg:mb-6">
                    {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        // Auto-close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                // Close mobile sidebar when switching to desktop
                const sidebarElement = document.querySelector('[x-data*="sidebarOpen"]');
                if (sidebarElement && sidebarElement.__x) {
                    sidebarElement.__x.$data.sidebarOpen = false;
                }
            }
        });

        function notificationDropdown() {
            return {
                open: false,
                notifications: [],
                unreadCount: 0,
                
                init() {
                    this.fetchNotifications();
                    // Fetch notifications every 30 seconds
                    setInterval(() => this.fetchNotifications(), 30000);
                },
                
                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.fetchNotifications();
                    }
                },
                
                close() {
                    this.open = false;
                },
                
                async fetchNotifications() {
                    try {
                        const response = await fetch('{{ route("notifications.get") }}');
                        const data = await response.json();
                        this.notifications = data.notifications.data || [];
                        this.unreadCount = data.unread_count || 0;
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    }
                },
                
                async markAsRead(notification) {
                    if (!notification.read_at) {
                        try {
                            await fetch(`/notifications/${notification.id}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json'
                                }
                            });
                            notification.read_at = new Date();
                            this.unreadCount--;
                        } catch (error) {
                            console.error('Failed to mark notification as read:', error);
                        }
                    }
                    
                    // Navigate to action URL if available
                    if (notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                },
                
                async markAllAsRead() {
                    try {
                        await fetch('/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            }
                        });
                        this.notifications.forEach(n => n.read_at = new Date());
                        this.unreadCount = 0;
                    } catch (error) {
                        console.error('Failed to mark all notifications as read:', error);
                    }
                },
                
                formatTime(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diff = Math.floor((now - date) / 1000); // difference in seconds
                    
                    if (diff < 60) return 'Baru saja';
                    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
                    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
                    if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
                    
                    return date.toLocaleDateString('id-ID');
                }
            }
        }
    </script>
</body>
</html>