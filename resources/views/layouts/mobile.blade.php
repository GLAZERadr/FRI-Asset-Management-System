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
    
    <!-- QR Scanner Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js"></script>
    
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
        
        .bottom-nav {
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .scan-button {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: transform 0.1s;
        }
        
        .scan-button:active {
            transform: scale(0.95);
        }

        /* QR Scanner Styles */
        .qr-scanner-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            flex-direction: column;
        }

        .qr-scanner-header {
            background: rgba(0, 0, 0, 0.7);
            padding: 1rem;
            display: flex;
            justify-content: between;
            align-items: center;
            color: white;
        }

        .qr-scanner-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #qr-video {
            width: 100%;
            height: auto;
            max-width: 400px;
            border-radius: 8px;
        }

        .qr-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 2px solid #10b981;
            border-radius: 8px;
            pointer-events: none;
        }

        .qr-overlay::before,
        .qr-overlay::after,
        .qr-overlay-corner::before,
        .qr-overlay-corner::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #10b981;
        }

        .qr-overlay::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .qr-overlay::after {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }

        .qr-overlay-corner::before {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
        }

        .qr-overlay-corner::after {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }

        .qr-scanner-footer {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem 1rem;
            text-align: center;
            color: white;
        }

        .qr-result {
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff40;
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 pb-20">
    <!-- Mobile Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- User Info -->
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-gray-300 overflow-hidden">
                        <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="font-semibold text-sm text-gray-900">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', Auth::user()->division ?? '')) }}</div>
                    </div>
                </div>
                
                <!-- Notification Bell -->
                <div class="relative" x-data="notificationDropdown()">
                    <button @click="toggle()" class="relative p-2 text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" class="notification-badge" x-text="unreadCount"></span>
                    </button>
                    
                    <!-- Mobile Notification Dropdown -->
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
                        
                        <div class="max-h-80 overflow-y-auto">
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
                                                     xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <svg x-show="notification.type === 'approval_result'" 
                                                     xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                            <p class="text-xs text-gray-600 mt-1" x-text="notification.message"></p>
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

    <!-- Main Content -->
    <main class="px-4 py-4">
        @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>

    <!-- QR Scanner Modal -->
    <div id="qr-scanner-modal" class="qr-scanner-modal" style="display: none;" x-data="qrScanner()">
        <!-- Header -->
        <div class="qr-scanner-header">
            <h2 class="text-lg font-semibold flex-1">Scan QR Code</h2>
            <button @click="closeScanner()" class="text-white hover:text-gray-300 p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scanner Container -->
        <div class="qr-scanner-container">
            <video id="qr-video" autoplay playsinline></video>
            <div class="qr-overlay">
                <div class="qr-overlay-corner"></div>
            </div>
        </div>

        <!-- Result Display -->
        <div x-show="result" class="qr-result" x-transition>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">QR Code Terdeteksi!</p>
                    <p class="text-sm opacity-90" x-text="result"></p>
                </div>
                <div x-show="loading" class="loading-spinner"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="qr-scanner-footer">
            <p class="text-sm mb-2">Arahkan kamera ke QR code pada aset</p>
            <div class="flex justify-center space-x-4">
                <button @click="toggleFlashlight()" x-show="hasFlashlight" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm">
                    <span x-text="flashlightOn ? 'Matikan Flash' : 'Nyalakan Flash'"></span>
                </button>
                <button @click="switchCamera()" x-show="hasMultipleCameras" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm">
                    Ganti Kamera
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white bottom-nav border-t border-gray-200">
        <div class="flex items-center justify-around py-2">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center p-2 {{ request()->routeIs('dashboard') ? 'text-green-600' : 'text-gray-500' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs mt-1">Home</span>
            </a>

            <!-- Riwayat -->
            <a href="{{ route('pengajuan.daftar') }}" class="flex flex-col items-center p-2 {{ request()->routeIs('pengajuan.*') ? 'text-green-600' : 'text-gray-500' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs mt-1">Riwayat</span>
            </a>

            <!-- Scan Button (Center) -->
            <button onclick="window.qrScannerInstance.openScanner()" class="scan-button w-14 h-14 rounded-full flex items-center justify-center -mt-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </button>

            <!-- Notifikasi -->
            <a href="#" onclick="event.preventDefault(); document.querySelector('[x-data*=notificationDropdown] button').click()" class="flex flex-col items-center p-2 text-gray-500 relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="text-xs mt-1">Notifikasi</span>
            </a>

            <!-- Akun -->
            <div class="flex flex-col items-center p-2 text-gray-500" x-data="{ open: false }">
                <button @click="open = !open" class="flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">Akun</span>
                </button>
                
                <!-- Profile Dropdown -->
                <div x-show="open" @click.away="open = false" x-transition 
                     class="absolute bottom-full right-4 mb-2 w-48 bg-white rounded-lg shadow-lg py-2"
                     style="display: none;">
                    <div class="px-4 py-2 border-b">
                        <div class="font-semibold text-sm">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
    
    <script>
        // Notification Dropdown Component
        function notificationDropdown() {
            return {
                open: false,
                notifications: [],
                unreadCount: 0,
                
                init() {
                    this.fetchNotifications();
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
                    const diff = Math.floor((now - date) / 1000);
                    
                    if (diff < 60) return 'Baru saja';
                    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
                    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
                    if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
                    
                    return date.toLocaleDateString('id-ID');
                }
            }
        }

        // QR Scanner Component
        function qrScanner() {
            return {
                scanner: null,
                result: '',
                loading: false,
                hasFlashlight: false,
                flashlightOn: false,
                hasMultipleCameras: false,
                currentCameraId: null,
                cameras: [],
                
                async openScanner() {
                    try {
                        // Check camera permission
                        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        stream.getTracks().forEach(track => track.stop());
                        
                        // Show modal
                        document.getElementById('qr-scanner-modal').style.display = 'flex';
                        
                        // Initialize scanner
                        await this.initializeScanner();
                    } catch (error) {
                        console.error('Camera access denied:', error);
                        alert('Akses kamera diperlukan untuk scanning QR code. Pastikan untuk memberikan izin kamera.');
                    }
                },
                
                async initializeScanner() {
                    try {
                        const video = document.getElementById('qr-video');
                        
                        // Get available cameras
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.cameras = devices.filter(device => device.kind === 'videoinput');
                        this.hasMultipleCameras = this.cameras.length > 1;
                        
                        // Use back camera if available
                        const backCamera = this.cameras.find(camera => 
                            camera.label.toLowerCase().includes('back') || 
                            camera.label.toLowerCase().includes('rear')
                        );
                        this.currentCameraId = backCamera ? backCamera.deviceId : this.cameras[0]?.deviceId;
                        
                        // Initialize QR Scanner
                        this.scanner = new QrScanner(
                            video,
                            result => this.onScanSuccess(result),
                            {
                                onDecodeError: error => {
                                    // Silent error handling - normal when no QR code is visible
                                },
                                highlightScanRegion: false,
                                highlightCodeOutline: false,
                                preferredCamera: this.currentCameraId ? 'user' : 'environment'
                            }
                        );
                        
                        // Check for flashlight support
                        this.hasFlashlight = await QrScanner.hasFlash();
                        
                        // Start scanning
                        await this.scanner.start();
                        
                    } catch (error) {
                        console.error('Failed to initialize scanner:', error);
                        alert('Gagal menginisialisasi scanner. Pastikan browser mendukung kamera.');
                    }
                },
                
                async onScanSuccess(result) {
                    this.result = result.data;
                    this.loading = true;
                    
                    // Stop scanner temporarily
                    if (this.scanner) {
                        this.scanner.stop();
                    }
                    
                    try {
                        // Process the QR code result
                        await this.processQRResult(result.data);
                    } catch (error) {
                        console.error('Failed to process QR result:', error);
                        alert('Gagal memproses hasil scan QR code.');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async processQRResult(qrData) {
                    try {
                        // Send QR data to backend for processing
                        const response = await fetch('/qr/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ qr_data: qrData })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            // Success - redirect to monitoring page
                            if (data.redirect_url) {
                                this.closeScanner();
                                window.location.href = data.redirect_url;
                                return;
                            }
                        } else {
                            throw new Error(data.message || 'Failed to process QR code');
                        }
                    } catch (error) {
                        console.error('QR processing error:', error);
                        
                        // Fallback: try to determine if it's a room code or asset ID
                        // Room codes typically follow XXXX-XXXX pattern (e.g., TULT-0901)
                        const roomCodePattern = /^[A-Z]{3,}-\d{3,}$/i;
                        // Asset IDs typically follow XXXX-XXX-XXX pattern (e.g., T0901-MEJ-001)
                        const assetIdPattern = /^[A-Z]\d+-[A-Z]{3}-\d{3}$/i;
                        
                        if (roomCodePattern.test(qrData)) {
                            // Looks like a room code
                            this.closeScanner();
                            window.location.href = `/pemantauan/monitoring/${qrData}`;
                            return;
                        } else if (assetIdPattern.test(qrData)) {
                            // Looks like an asset ID - extract room code
                            // Assuming asset ID format like T0901-MEJ-001, we need to find the room
                            // For now, redirect to general asset view since we can't determine room
                            alert(`Asset ID detected: ${qrData}. Please scan the room QR code for monitoring.`);
                        } else {
                            // Unknown format
                            alert('QR code tidak dikenali. Pastikan QR code valid untuk ruangan atau aset.');
                        }
                        
                        // Restart scanner after a delay
                        setTimeout(() => {
                            if (this.scanner) {
                                this.scanner.start();
                            }
                            this.result = '';
                        }, 3000);
                    }
                },
                
                closeScanner() {
                    if (this.scanner) {
                        this.scanner.stop();
                        this.scanner.destroy();
                        this.scanner = null;
                    }
                    
                    document.getElementById('qr-scanner-modal').style.display = 'none';
                    this.result = '';
                    this.loading = false;
                    this.flashlightOn = false;
                },
                
                async toggleFlashlight() {
                    if (this.scanner && this.hasFlashlight) {
                        try {
                            if (this.flashlightOn) {
                                await this.scanner.turnFlashOff();
                            } else {
                                await this.scanner.turnFlashOn();
                            }
                            this.flashlightOn = !this.flashlightOn;
                        } catch (error) {
                            console.error('Failed to toggle flashlight:', error);
                        }
                    }
                },
                
                async switchCamera() {
                    if (this.hasMultipleCameras && this.scanner) {
                        try {
                            const currentIndex = this.cameras.findIndex(cam => cam.deviceId === this.currentCameraId);
                            const nextIndex = (currentIndex + 1) % this.cameras.length;
                            this.currentCameraId = this.cameras[nextIndex].deviceId;
                            
                            await this.scanner.setCamera(this.currentCameraId);
                        } catch (error) {
                            console.error('Failed to switch camera:', error);
                        }
                    }
                }
            }
        }

        // Global QR Scanner instance
        document.addEventListener('DOMContentLoaded', function() {
            window.qrScannerInstance = Alpine.reactive({
                openScanner() {
                    // Find the QR scanner component and call its openScanner method
                    const modal = document.getElementById('qr-scanner-modal');
                    if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
                        modal._x_dataStack[0].openScanner();
                    }
                }
            });
        });

        // Handle browser back button when scanner is open
        window.addEventListener('popstate', function() {
            const modal = document.getElementById('qr-scanner-modal');
            if (modal.style.display !== 'none') {
                const scannerComponent = modal._x_dataStack && modal._x_dataStack[0];
                if (scannerComponent) {
                    scannerComponent.closeScanner();
                }
            }
        });

        // Handle page visibility change (pause scanner when page is hidden)
        document.addEventListener('visibilitychange', function() {
            const modal = document.getElementById('qr-scanner-modal');
            const scannerComponent = modal._x_dataStack && modal._x_dataStack[0];
            
            if (scannerComponent && scannerComponent.scanner) {
                if (document.hidden) {
                    scannerComponent.scanner.stop();
                } else if (modal.style.display !== 'none') {
                    scannerComponent.scanner.start();
                }
            }
        });
    </script>
</body>
</html>