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
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <!-- Logo -->
            <div class="p-4 border-b">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo fri.png') }}" alt="Logo" class="h-16">
                    <div class="ml-2 text-green-700 font-bold">
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-4">
                <a href="{{ route('dashboard') }}" class="flex items-center py-3 px-4 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                @php
                    $disabled = true; // or based on logic like Auth::user()->cannot('access-pemantauan')
                @endphp

                <a 
                    @unless($disabled) href="{{ route('pemantauan') }}" @endunless
                    class="flex items-center py-3 px-4 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pemantauan') ? 'bg-gray-100' : '' }} {{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Pemantauan
                </a>

                <a 
                    @unless($disabled) href="{{ route('perbaikan.aset') }}" @endunless
                    class="flex items-center py-3 px-4 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('perbaikan.aset') ? 'bg-gray-100' : '' }} {{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Perbaikan Aset
                </a>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center w-full py-3 px-4 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('pengajuan.*') ? 'bg-gray-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Pengajuan Perbaikan
                        <svg x-show="!open" class="ml-auto h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="open" class="ml-auto h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" class="pl-4" style="display: none;">
                        @can('show_maintenance_request')
                            <a href="{{ route('pengajuan.daftar') }}" class="flex items-center py-2 px-4 text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pengajuan.daftar') ? 'bg-gray-100' : '' }}">
                                Daftar Perbaikan Aset
                            </a>
                        @endcan
                        @can('create_criteria')
                            <a href="{{ route('kriteria.create') }}" class="flex items-center py-2 px-4 text-gray-600 hover:bg-gray-100 {{ request()->routeIs('kriteria.create') ? 'bg-gray-100' : '' }}">
                                kriteria
                            </a>
                        @endcan
                        @can('create_maintenance_request')
                        <a href="{{ route('pengajuan.baru') }}" class="flex items-center py-2 px-4 text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pengajuan.baru') ? 'bg-gray-100' : '' }}">
                            Pengajuan
                        </a>
                        @endcan
                        @can('create_payment')
                            <a href="{{ route('pembayaran.index') }}" class="flex items-center py-2 px-4 text-gray-600 hover:bg-gray-100 {{ request()->routeIs('pengajuan.index') ? 'bg-gray-100' : '' }}">
                                Pembayaran
                            </a>
                        @endcan
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                    @csrf
                    <button type="submit" class="flex items-center py-3 px-4 text-gray-700 hover:bg-gray-100 w-full text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-4 py-3 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold">
                        @yield('header', 'Manajemen Aset')
                    </h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1">
                                <div class="h-10 w-10 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar">
                                </div>
                                <div class="text-sm text-left">
                                    <div class="font-semibold">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ ucwords(str_replace('_', ' ', Auth::user()->division ?? '')) }}
                                    </div>
                                </div>
                            </button>
                        </div>

                        <!-- Notification Bell -->
                        <div class="relative" x-data="notificationDropdown()">
                            <button @click="toggle()" class="relative text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span x-show="unreadCount > 0" class="notification-badge" x-text="unreadCount"></span>
                            </button>
                            
                            <!-- Notification Dropdown -->
                            <div x-show="open" 
                                 @click.away="close()" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50"
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
                                                <div class="ml-3 flex-1">
                                                    <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                                    <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="formatTime(notification.created_at)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="py-2 px-4 bg-gray-50 border-t">
                                    <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                        Lihat semua notifikasi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @if (session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                    {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
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