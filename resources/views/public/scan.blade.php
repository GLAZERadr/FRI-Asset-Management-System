<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Scanner - Asset Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- QR Scanner Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js"></script>
    
    <style>
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

        .landing-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .main-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .scan-button-main {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1.5rem 3rem;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
            margin-top: 2rem;
            width: 100%;
        }

        .scan-button-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.4);
        }

        .scan-button-main:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Landing Page -->
    <div class="landing-container" id="landing-page">
        <div class="main-card">
            <div class="text-center mb-6">
                <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Asset QR Scanner</h1>
                <p class="text-gray-600">Scan QR code untuk monitoring aset</p>
            </div>
            
            <button onclick="openScanner()" class="scan-button-main">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                Mulai Scan QR Code
            </button>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500 mb-3">Sudah punya akun?</p>
                <a href="/login" class="text-green-600 hover:text-green-700 font-medium">Login di sini</a>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Cara Penggunaan:</h3>
                <ol class="text-left text-sm text-gray-600 space-y-2">
                    <li class="flex items-start">
                        <span class="bg-green-100 text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">1</span>
                        Klik tombol "Mulai Scan QR Code"
                    </li>
                    <li class="flex items-start">
                        <span class="bg-green-100 text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">2</span>
                        Arahkan kamera ke QR code pada aset atau ruangan
                    </li>
                    <li class="flex items-start">
                        <span class="bg-green-100 text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">3</span>
                        Tunggu hingga QR code terbaca otomatis
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div id="qr-scanner-modal" class="qr-scanner-modal" style="display: none;">
        <!-- Header -->
        <div class="qr-scanner-header">
            <h2 class="text-lg font-semibold flex-1">Scan QR Code</h2>
            <button onclick="closeScanner()" class="text-white hover:text-gray-300 p-2">
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
        <div id="qr-result" class="qr-result" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">QR Code Terdeteksi!</p>
                    <p class="text-sm opacity-90" id="qr-result-text"></p>
                </div>
                <div id="loading-spinner" class="loading-spinner" style="display: none;"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="qr-scanner-footer">
            <p class="text-sm mb-2">Arahkan kamera ke QR code pada aset atau ruangan</p>
            <div class="flex justify-center space-x-4">
                <button onclick="switchCamera()" id="switch-camera-btn" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm" style="display: none;">
                    Ganti Kamera
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let scanner = null;
        let currentCameraId = null;
        let cameras = [];

        // Get CSRF token function
        function getCSRFToken() {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) {
                console.error('CSRF token not found');
                return null;
            }
            return tokenMeta.getAttribute('content');
        }

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('QR Scanner loaded');
            console.log('CSRF Token:', getCSRFToken());
        });

        function openScanner() {
            console.log('Opening scanner...');
            initScanner();
        }

        async function initScanner() {
            try {
                // Check camera permission - specifically request back camera
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } // Request back camera initially
                });
                stream.getTracks().forEach(track => track.stop());
                
                // Show modal
                const modal = document.getElementById('qr-scanner-modal');
                const landingPage = document.getElementById('landing-page');
                
                if (modal) {
                    modal.style.display = 'flex';
                }
                if (landingPage) {
                    landingPage.style.display = 'none';
                }
                
                // Initialize scanner
                await initializeScanner();
            } catch (error) {
                console.error('Camera access denied:', error);
                if (error.name === 'NotAllowedError') {
                    alert('Akses kamera diperlukan untuk scanning QR code. Silakan izinkan akses kamera dan coba lagi.');
                } else if (error.name === 'NotSecureError') {
                    alert('Kamera hanya bisa diakses melalui HTTPS. Silakan gunakan koneksi yang aman.');
                } else {
                    alert('Gagal mengakses kamera: ' + error.message);
                }
            }
        }

        async function initializeScanner() {
            try {
                const video = document.getElementById('qr-video');
                if (!video) {
                    throw new Error('Video element not found');
                }
                
                // Get available cameras
                const devices = await navigator.mediaDevices.enumerateDevices();
                cameras = devices.filter(device => device.kind === 'videoinput');
                
                const switchCameraBtn = document.getElementById('switch-camera-btn');
                if (cameras.length > 1 && switchCameraBtn) {
                    switchCameraBtn.style.display = 'block';
                }
                
                // Find back camera more comprehensively
                const backCamera = cameras.find(camera => {
                    const label = camera.label.toLowerCase();
                    return label.includes('back') || 
                           label.includes('rear') || 
                           label.includes('environment') ||
                           label.includes('facing back') ||
                           label.includes('camera 0') || // Often the main camera on mobile
                           (label.includes('camera') && !label.includes('front') && !label.includes('user'));
                });
                
                // Set camera preference
                let cameraPreference = 'environment'; // Default to back camera
                if (backCamera) {
                    currentCameraId = backCamera.deviceId;
                } else if (cameras.length > 0) {
                    // If no back camera found, use the first available camera
                    currentCameraId = cameras[0].deviceId;
                    cameraPreference = 'user';
                }
                
                console.log('Available cameras:', cameras.map(c => c.label));
                console.log('Selected camera:', backCamera?.label || 'Default environment camera');
                
                // Initialize QR Scanner with proper camera settings
                scanner = new QrScanner(
                    video,
                    function(result) {
                        onScanSuccess(result);
                    },
                    {
                        onDecodeError: function(error) {
                            // Silent error handling - normal when no QR code is visible
                        },
                        highlightScanRegion: false,
                        highlightCodeOutline: false,
                        preferredCamera: cameraPreference, // 'environment' for back, 'user' for front
                        maxScansPerSecond: 5, // Optimize performance
                        calculateScanRegion: function(video) {
                            // Define scan region (center square)
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
                
                // If we have a specific camera ID, try to set it after initialization
                if (currentCameraId && backCamera) {
                    try {
                        await scanner.setCamera(currentCameraId);
                    } catch (error) {
                        console.warn('Failed to set specific camera, using default:', error);
                    }
                }
                
                // Start scanning
                await scanner.start();
                
                console.log('QR Scanner initialized with back camera preference');
                
            } catch (error) {
                console.error('Failed to initialize scanner:', error);
                
                // Fallback: try with basic settings
                try {
                    const video = document.getElementById('qr-video');
                    scanner = new QrScanner(
                        video,
                        function(result) {
                            onScanSuccess(result);
                        },
                        {
                            preferredCamera: 'environment', // Still try back camera
                            onDecodeError: function() {} // Silent
                        }
                    );
                    await scanner.start();
                    console.log('QR Scanner initialized with fallback settings');
                } catch (fallbackError) {
                    console.error('Fallback scanner initialization failed:', fallbackError);
                    alert('Gagal menginisialisasi scanner. Pastikan browser mendukung kamera.');
                }
            }
        }

        function onScanSuccess(result) {
            const resultElement = document.getElementById('qr-result');
            const resultText = document.getElementById('qr-result-text');
            const spinner = document.getElementById('loading-spinner');
            
            if (resultText) {
                resultText.textContent = result.data;
            }
            if (resultElement) {
                resultElement.style.display = 'block';
            }
            if (spinner) {
                spinner.style.display = 'block';
            }
            
            // Stop scanner temporarily
            if (scanner) {
                scanner.stop();
            }
            
            // Process QR result
            processQRResult(result.data);
        }

        function processQRResult(qrData) {
            console.log('Processing QR data:', qrData);
            
            const csrfToken = getCSRFToken();
            if (!csrfToken) {
                showError('CSRF token tidak ditemukan. Silakan refresh halaman.');
                return;
            }

            fetch('/public/qr/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_data: qrData })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
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
                
                return response.json();
            })
            .then(data => {
                console.log('QR process response:', data);
                
                if (data.success) {
                    // Handle different response types
                    if (data.type === 'room_monitoring') {
                        // Room code detected - redirect to monitoring form
                        showSuccess(`Ruangan ${data.kode_ruangan} ditemukan! Mengarahkan ke form monitoring...`);
                        setTimeout(function() {
                            window.location.href = data.redirect_url;
                        }, 1500);
                        return;
                    } else if (data.type === 'asset_found') {
                        // Individual asset found - redirect to room monitoring
                        showSuccess(`Asset ${data.asset_id} ditemukan! Mengarahkan ke monitoring ruangan ${data.kode_ruangan}...`);
                        setTimeout(function() {
                            window.location.href = data.redirect_url;
                        }, 2000);
                        return;
                    } else if (data.redirect_url) {
                        // Generic redirect
                        setTimeout(function() {
                            window.location.href = data.redirect_url;
                        }, 1500);
                        return;
                    }
                } else {
                    throw new Error(data.message || 'Failed to process QR code');
                }
            })
            .catch(error => {
                console.error('QR processing error:', error);
                showError(error.message || 'Gagal memproses hasil scan QR code.');
                
                // Restart scanner after error
                setTimeout(function() {
                    if (scanner) {
                        scanner.start();
                    }
                    
                    // Safely hide elements
                    const resultElement = document.getElementById('qr-result');
                    const spinnerElement = document.getElementById('loading-spinner');
                    
                    if (resultElement) {
                        resultElement.style.display = 'none';
                    }
                    if (spinnerElement) {
                        spinnerElement.style.display = 'none';
                    }
                }, 3000);
            });
        }

        function showSuccess(message) {
            const resultElement = document.getElementById('qr-result');
            if (resultElement) {
                resultElement.style.background = '#10b981';
                resultElement.innerHTML = 
                    '<div class="text-center">' +
                        '<div class="text-white mb-2">✅ Berhasil!</div>' +
                        '<p class="text-sm text-white">' + message + '</p>' +
                    '</div>';
            }
        }

        function showError(message) {
            const resultElement = document.getElementById('qr-result');
            if (resultElement) {
                resultElement.style.background = '#e74c3c';
                resultElement.innerHTML = 
                    '<div class="text-center">' +
                        '<div class="text-white mb-2">❌ Error</div>' +
                        '<p class="text-sm text-white">' + message + '</p>' +
                        '<p class="text-xs opacity-75 mt-2 text-white">Scanner akan restart dalam 3 detik...</p>' +
                    '</div>';
            }
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        function closeScanner() {
            if (scanner) {
                scanner.stop();
                scanner.destroy();
                scanner = null;
            }
            
            const modal = document.getElementById('qr-scanner-modal');
            const landingPage = document.getElementById('landing-page');
            const resultElement = document.getElementById('qr-result');
            
            if (modal) {
                modal.style.display = 'none';
            }
            if (landingPage) {
                landingPage.style.display = 'flex';
            }
            if (resultElement) {
                resultElement.style.display = 'none';
            }
        }

        function switchCamera() {
            if (cameras.length > 1 && scanner) {
                try {
                    const currentIndex = cameras.findIndex(function(cam) {
                        return cam.deviceId === currentCameraId;
                    });
                    const nextIndex = (currentIndex + 1) % cameras.length;
                    const nextCamera = cameras[nextIndex];
                    currentCameraId = nextCamera.deviceId;
                    
                    console.log('Switching to camera:', nextCamera.label);
                    scanner.setCamera(currentCameraId);
                } catch (error) {
                    console.error('Failed to switch camera:', error);
                    alert('Gagal mengganti kamera. Silakan coba lagi.');
                }
            }
        }

        // Handle browser back button
        window.addEventListener('popstate', function() {
            const modal = document.getElementById('qr-scanner-modal');
            if (modal && modal.style.display !== 'none') {
                closeScanner();
            }
        });

        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (scanner) {
                if (document.hidden) {
                    scanner.stop();
                } else {
                    const modal = document.getElementById('qr-scanner-modal');
                    if (modal && modal.style.display !== 'none') {
                        scanner.start();
                    }
                }
            }
        });
    </script>
</body>
</html>