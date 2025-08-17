<!-- auth.blade.php - Merged QR Scanner -->
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- QR Scanner Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js"></script>
    
    <style>
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
            justify-content: space-between;
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

        /* Tab Styles */
        .scanner-tabs {
            background: rgba(0, 0, 0, 0.8);
            padding: 0;
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .scanner-tab {
            flex: 1;
            padding: 1rem;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .scanner-tab.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: #10b981;
        }

        .scanner-tab:hover:not(.active) {
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Mobile QR Quick Access Button */
        .qr-quick-access {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            margin-top: 1rem;
        }

        .qr-quick-access:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Show QR access on mobile or testing mode */
        .mobile-qr-section {
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-qr-section {
                display: block;
            }
        }

        /* Testing mode - show on desktop too */
        .mobile-qr-section.testing {
            display: block !important;
        }

        /* Mode indicator colors */
        .monitoring-mode .qr-overlay {
            border-color: #3b82f6;
        }
        .monitoring-mode .qr-overlay::before,
        .monitoring-mode .qr-overlay::after,
        .monitoring-mode .qr-overlay-corner::before,
        .monitoring-mode .qr-overlay-corner::after {
            border-color: #3b82f6;
        }

        .perbaikan-mode .qr-overlay {
            border-color: #ef4444;
        }
        .perbaikan-mode .qr-overlay::before,
        .perbaikan-mode .qr-overlay::after,
        .perbaikan-mode .qr-overlay-corner::before,
        .perbaikan-mode .qr-overlay-corner::after {
            border-color: #ef4444;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo fri.png') }}" alt="Fakultas Rekayasa Industri" class="h-30" id="logo-toggle">
            </div>
            
            @yield('content')
            
            <!-- Mobile QR Scanner Quick Access -->
            <div class="mobile-qr-section mt-4 px-4" id="qr-access-section">
                <div class="text-center">
                    <p class="text-gray-600 text-sm mb-3">
                        Akses langsung tanpa login:
                    </p>
                    
                    <!-- Quick Access Buttons -->
                    <div class="space-y-2">
                        <button onclick="openQRScanner('perbaikan')" class="qr-quick-access bg-blue-600 hover:bg-blue-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Scan Perbaikan Aset
                        </button>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">
                        Pilih mode yang sesuai dengan kebutuhan Anda
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal with Tabs -->
    <div id="qr-scanner-modal" class="qr-scanner-modal" style="display: none;" x-data="qrScanner()">
        <!-- Header -->
        <div class="qr-scanner-header">
            <div class="flex-1">
                <h2 class="text-lg font-semibold" x-text="mode === 'monitoring' ? 'Scan QR - Monitoring Aset' : 'Scan QR - Lapor Kerusakan'"></h2>
                <p class="text-sm opacity-75" x-text="mode === 'monitoring' ? 'Scan QR code ruangan atau aset' : 'Scan QR code aset yang rusak'"></p>
            </div>
            <button @click="closeScanner()" class="text-white hover:text-gray-300 p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scanner Container -->
        <div class="qr-scanner-container" :class="mode === 'monitoring' ? 'monitoring-mode' : 'perbaikan-mode'">
            <video id="qr-video" autoplay playsinline></video>
            <div class="qr-overlay">
                <div class="qr-overlay-corner"></div>
            </div>
        </div>

        <!-- Result Display -->
        <div x-show="result" class="qr-result" x-transition :style="`background: ${mode === 'monitoring' ? '#3b82f6' : '#ef4444'}`">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">QR Code Terdeteksi!</p>
                    <p class="text-sm opacity-90" x-text="result"></p>
                    <p class="text-xs opacity-75 mt-1" x-text="statusMessage"></p>
                </div>
                <div x-show="loading" class="loading-spinner"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="qr-scanner-footer">
            <div x-show="mode === 'perbaikan'">
                <p class="text-sm mb-2">Mode: <strong>Pelaporan Kerusakan</strong></p>
                <p class="text-xs opacity-75 mb-4">Arahkan kamera ke QR code aset yang mengalami kerusakan</p>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button @click="switchCamera()" x-show="hasMultipleCameras" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm">
                    Ganti Kamera
                </button>
            </div>
        </div>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>

    <script>
        // Global QR Scanner opener function
        function openQRScanner(mode = 'monitoring') {
            const modal = document.getElementById('qr-scanner-modal');
            if (modal && modal._x_dataStack && modal._x_dataStack[0]) {
                modal._x_dataStack[0].openScanner(mode);
            } else {
                // Retry after a short delay if Alpine isn't ready
                setTimeout(() => openQRScanner(mode), 100);
            }
        }

        // Enhanced QR Scanner Component with Mode Switching
        function qrScanner() {
            return {
                scanner: null,
                result: '',
                loading: false,
                statusMessage: '',
                hasMultipleCameras: false,
                currentCameraId: null,
                cameras: [],
                mode: 'monitoring', // 'monitoring' or 'perbaikan'
                
                async openScanner(initialMode = 'monitoring') {
                    this.mode = initialMode;
                    
                    try {
                        // Check camera permission
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: 'environment' } // Request back camera initially
                        });
                        stream.getTracks().forEach(track => track.stop());
                        
                        // Show modal
                        document.getElementById('qr-scanner-modal').style.display = 'flex';
                        
                        // Initialize scanner
                        await this.initializeScanner();
                    } catch (error) {
                        console.error('Camera access denied:', error);
                        if (error.name === 'NotAllowedError') {
                            alert('Akses kamera diperlukan untuk scanning QR code. Pastikan untuk memberikan izin kamera.');
                        } else if (error.name === 'NotSecureError') {
                            alert('Kamera hanya bisa diakses melalui HTTPS. Silakan gunakan koneksi yang aman.');
                        } else {
                            alert('Gagal mengakses kamera: ' + error.message);
                        }
                    }
                },
                
                switchMode(newMode) {
                    if (this.mode !== newMode) {
                        this.mode = newMode;
                        this.result = '';
                        this.statusMessage = '';
                        this.loading = false;
                        
                        // Restart scanner if it's running
                        if (this.scanner) {
                            this.scanner.start();
                        }
                    }
                },
                
                async initializeScanner() {
                    try {
                        const video = document.getElementById('qr-video');
                        
                        // Get available cameras
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.cameras = devices.filter(device => device.kind === 'videoinput');
                        this.hasMultipleCameras = this.cameras.length > 1;
                        
                        // Find back camera
                        const backCamera = this.cameras.find(camera => {
                            const label = camera.label.toLowerCase();
                            return label.includes('back') || 
                                label.includes('rear') || 
                                label.includes('environment') ||
                                label.includes('facing back') ||
                                label.includes('camera 0') ||
                                (label.includes('camera') && !label.includes('front') && !label.includes('user'));
                        });
                        
                        // Set camera preference
                        let cameraPreference = 'environment';
                        if (backCamera) {
                            this.currentCameraId = backCamera.deviceId;
                        } else if (this.cameras.length > 0) {
                            this.currentCameraId = this.cameras[0].deviceId;
                            cameraPreference = 'user';
                        }
                        
                        console.log('Available cameras:', this.cameras.map(c => c.label));
                        console.log('Selected camera:', backCamera?.label || 'Default');
                        
                        // Initialize QR Scanner
                        this.scanner = new QrScanner(
                            video,
                            result => this.onScanSuccess(result),
                            {
                                onDecodeError: error => {
                                    // Silent error handling
                                },
                                highlightScanRegion: false,
                                highlightCodeOutline: false,
                                preferredCamera: cameraPreference,
                                maxScansPerSecond: 5,
                                calculateScanRegion: (video) => {
                                    const smallestDimension = Math.min(video.videoWidth, video.videoHeight);
                                    const scanRegionSize = Math.round(0.7 * smallestDimension);
                                    
                                    return {
                                        x: Math.round((video.videoWidth - scanRegionSize) / 2),
                                        y: Math.round((video.videoHeight - scanRegionSize) / 2),
                                        width: scanRegionSize,
                                        height: scanRegionSize,
                                    };
                                }
                            }
                        );
                        
                        // Set specific camera if available
                        if (this.currentCameraId && backCamera) {
                            try {
                                await this.scanner.setCamera(this.currentCameraId);
                            } catch (error) {
                                console.warn('Failed to set specific camera, using default:', error);
                            }
                        }
                        
                        // Start scanning
                        await this.scanner.start();
                        console.log('QR Scanner initialized');
                        
                    } catch (error) {
                        console.error('Failed to initialize scanner:', error);
                        alert('Gagal menginisialisasi scanner. Pastikan browser mendukung kamera.');
                    }
                },
                
                async onScanSuccess(result) {
                    this.result = result.data;
                    this.loading = true;
                    this.statusMessage = this.mode === 'monitoring' ? 'Memproses untuk monitoring...' : 'Memproses untuk pelaporan...';
                    
                    // Stop scanner temporarily
                    if (this.scanner) {
                        this.scanner.stop();
                    }
                    
                    try {
                        await this.processQRResult(result.data);
                    } catch (error) {
                        console.error('Failed to process QR result:', error);
                        this.showError('Gagal memproses hasil scan QR code.');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async processQRResult(qrData) {
                    try {
                        const endpoint = this.mode === 'monitoring' ? '/public/qr/process' : '/damage-report/qr-process';
                        
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ qr_data: qrData })
                        });
                        
                        if (response.status === 419) {
                            throw new Error('CSRF token expired. Silakan refresh halaman.');
                        }
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Server tidak mengembalikan JSON. Mungkin ada error di server.');
                        }
                        
                        const data = await response.json();
                        console.log('QR process response:', data);
                        
                        if (data.success) {
                            this.showSuccess(data.message || 'QR code berhasil diproses!');
                            
                            if (data.redirect_url) {
                                setTimeout(() => {
                                    this.closeScanner();
                                    window.location.href = data.redirect_url;
                                }, 1500);
                                return;
                            }
                        } else {
                            throw new Error(data.message || 'Failed to process QR code');
                        }
                    } catch (error) {
                        console.error('QR processing error:', error);
                        this.showError(error.message || 'Gagal memproses hasil scan QR code.');
                        
                        // Restart scanner after error
                        setTimeout(() => {
                            if (this.scanner) {
                                this.scanner.start();
                            }
                            this.result = '';
                            this.statusMessage = '';
                        }, 3000);
                    }
                },
                
                showSuccess(message) {
                    this.statusMessage = message;
                    // Update result display to green
                    const resultElement = document.querySelector('.qr-result');
                    if (resultElement) {
                        resultElement.style.background = '#10b981';
                    }
                },
                
                showError(message) {
                    this.statusMessage = message;
                    // Update result display to red
                    const resultElement = document.querySelector('.qr-result');
                    if (resultElement) {
                        resultElement.style.background = '#ef4444';
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
                    this.statusMessage = '';
                },
                
                async switchCamera() {
                    if (this.hasMultipleCameras && this.scanner) {
                        try {
                            const currentIndex = this.cameras.findIndex(cam => cam.deviceId === this.currentCameraId);
                            const nextIndex = (currentIndex + 1) % this.cameras.length;
                            const nextCamera = this.cameras[nextIndex];
                            this.currentCameraId = nextCamera.deviceId;
                            
                            console.log('Switching to camera:', nextCamera.label);
                            await this.scanner.setCamera(this.currentCameraId);
                        } catch (error) {
                            console.error('Failed to switch camera:', error);
                            alert('Gagal mengganti kamera. Silakan coba lagi.');
                        }
                    }
                }
            }
        }

        // Testing mode and mobile detection
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const isTestingMode = urlParams.get('test') === 'mobile' || 
                                 window.localStorage.getItem('qr_testing') === 'true';
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const shouldShow = isTestingMode || window.innerWidth <= 768 || isMobile;
            
            console.log('QR Scanner conditions:', { isTestingMode, isMobile, shouldShow, width: window.innerWidth });
            
            if (shouldShow) {
                const qrSection = document.getElementById('qr-access-section');
                if (qrSection) {
                    qrSection.classList.add('testing');
                    console.log('QR scanner section enabled');
                }
            }
            
            // Testing toggle (double-click logo)
            const logo = document.getElementById('logo-toggle');
            if (logo) {
                let clickCount = 0;
                logo.addEventListener('click', function() {
                    clickCount++;
                    setTimeout(() => { clickCount = 0; }, 500);
                    
                    if (clickCount === 2) {
                        console.log('Testing mode toggled');
                        const currentMode = window.localStorage.getItem('qr_testing') === 'true';
                        window.localStorage.setItem('qr_testing', (!currentMode).toString());
                        alert(`QR Testing mode ${!currentMode ? 'enabled' : 'disabled'}. Page will reload.`);
                        window.location.reload();
                    }
                });
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('qr-scanner-modal');
                if (modal && modal.style.display !== 'none') {
                    const scannerComponent = modal._x_dataStack && modal._x_dataStack[0];
                    if (scannerComponent) {
                        scannerComponent.closeScanner();
                    }
                }
            }
        });

        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            const modal = document.getElementById('qr-scanner-modal');
            if (modal && modal.style.display !== 'none' && modal._x_dataStack && modal._x_dataStack[0]) {
                const scannerComponent = modal._x_dataStack[0];
                if (scannerComponent.scanner) {
                    if (document.hidden) {
                        scannerComponent.scanner.stop();
                    } else {
                        scannerComponent.scanner.start();
                    }
                }
            }
        });
    </script>
</body>
</html>