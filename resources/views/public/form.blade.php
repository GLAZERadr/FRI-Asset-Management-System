<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring Asset - {{ $kodeRuangan }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .monitoring-container {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }

        .monitoring-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .monitoring-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .monitoring-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .monitoring-form-section {
            padding: 20px;
        }

        .monitoring-form-group {
            margin-bottom: 20px;
        }

        .monitoring-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .monitoring-form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            box-sizing: border-box;
        }

        .monitoring-form-control:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.1);
        }

        .monitoring-asset-list {
            margin-top: 20px;
        }

        .monitoring-asset-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .monitoring-asset-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .monitoring-asset-id {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .monitoring-asset-name {
            color: #666;
            font-size: 13px;
        }

        .monitoring-status-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .monitoring-status-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-size: 14px;
        }

        .monitoring-status-btn.active.baik {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
        }

        .monitoring-status-btn.active.butuh-perawatan {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .monitoring-status-btn:hover {
            border-color: #bbb;
        }

        .monitoring-description-group {
            margin-top: 12px;
            display: none;
        }

        .monitoring-description-group.show {
            display: block;
        }

        .monitoring-description-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 60px;
            box-sizing: border-box;
        }

        .monitoring-photo-upload {
            margin-top: 10px;
        }

        .monitoring-upload-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            border: none;
        }

        .monitoring-upload-btn:hover {
            background: #2980b9;
        }

        .monitoring-file-input {
            display: none;
        }

        .monitoring-photo-preview {
            margin-top: 10px;
            max-width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            display: none;
        }

        .monitoring-action-buttons {
            padding: 20px;
            display: flex;
            gap: 12px;
            background: white;
            border-top: 1px solid #eee;
            position: sticky;
            bottom: 0;
        }

        .monitoring-btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .monitoring-btn-save {
            background: #27ae60;
            color: white;
        }

        .monitoring-btn-save:hover {
            background: #229954;
        }

        .monitoring-btn-cancel {
            background: #e74c3c;
            color: white;
        }

        .monitoring-btn-cancel:hover {
            background: #c0392b;
        }

        .monitoring-selected-file {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .monitoring-asset-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }

        .monitoring-asset-photo-placeholder {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .room-info {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            margin: -1px 0 20px 0;
        }

        .room-info h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .room-info p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-prompt {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-prompt p {
            color: #0369a1;
            margin-bottom: 10px;
        }

        .login-prompt a {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 500;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .success-message {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        .success-message h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .success-message p {
            font-size: 14px;
            opacity: 0.9;
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
    </style>
</head>
<body>
    <div class="monitoring-container">
        <!-- Header -->
        <div class="monitoring-header">
            <button onclick="window.location.href='/'" class="back-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div>
                <h1>Monitoring Asset</h1>
                <p>Pemeriksaan Kondisi Aset</p>
            </div>
        </div>

        <!-- Room Info -->
        <div class="room-info">
            <h2>Ruangan: {{ $kodeRuangan }}</h2>
            <p>{{ $assets->count() }} aset ditemukan</p>
        </div>

        @if(session('success'))
            <div class="success-message">
                <h3>Laporan Berhasil Dikirim!</h3>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mx-4 mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Login Prompt -->
        <div class="login-prompt">
            <p>🔐 Untuk fitur lengkap dan riwayat monitoring</p>
            <a href="/login">Masuk ke akun Anda</a>
        </div>

        <form action="/public/monitoring/store" method="POST" enctype="multipart/form-data" id="monitoringForm">
            @csrf
            <input type="hidden" name="kode_ruangan" value="{{ $kodeRuangan }}">

            <div class="monitoring-form-section">
                <div class="monitoring-form-group">
                    <label>Nama Pelapor *</label>
                    <input type="text" name="nama_pelapor" class="monitoring-form-control" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="monitoring-form-group">
                    <label>Tanggal Laporan</label>
                    <input type="date" name="tanggal_laporan" class="monitoring-form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="monitoring-asset-list">
                    @foreach($assets as $index => $asset)
                    <div class="monitoring-asset-item">
                        <div class="monitoring-asset-header">
                            <div>
                                <div class="monitoring-asset-id">{{ $asset->asset_id }}</div>
                                <div class="monitoring-asset-name">{{ $asset->nama_asset }}</div>
                            </div>
                            <div style="text-align: center;">
                                @if($asset->foto_asset)
                                    <img src="{{ $asset->foto_asset }}" class="monitoring-asset-photo">
                                @else
                                    <div class="monitoring-asset-photo-placeholder">📷</div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="monitoring-status-buttons">
                            <button type="button" class="monitoring-status-btn" data-status="baik" data-asset="{{ $index }}">
                                ✅ Baik
                            </button>
                            <button type="button" class="monitoring-status-btn" data-status="butuh_perawatan" data-asset="{{ $index }}">
                                ⚠️ Butuh Perawatan
                            </button>
                        </div>
                        
                        <div class="monitoring-description-group" id="desc-{{ $index }}">
                            <textarea name="asset_data[{{ $index }}][deskripsi]" class="monitoring-description-input" placeholder="Deskripsi kerusakan atau masalah yang ditemukan..."></textarea>
                            <div class="monitoring-photo-upload">
                                <label for="photo-{{ $index }}" class="monitoring-upload-btn">📷 Upload Foto</label>
                                <input type="file" id="photo-{{ $index }}" name="asset_data[{{ $index }}][foto]" class="monitoring-file-input" accept="image/*">
                                <div class="monitoring-selected-file" id="file-name-{{ $index }}"></div>
                                <img class="monitoring-photo-preview" id="preview-{{ $index }}">
                            </div>
                        </div>
                        
                        <input type="hidden" name="asset_data[{{ $index }}][asset_id]" value="{{ $asset->asset_id }}">
                        <input type="hidden" name="asset_data[{{ $index }}][status]" id="status-{{ $index }}">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="monitoring-action-buttons">
                <button type="submit" class="monitoring-btn monitoring-btn-save">💾 Simpan Laporan</button>
                <button type="button" class="monitoring-btn monitoring-btn-cancel" onclick="window.location.href='/'">❌ Batal</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date
            document.querySelector('input[name="tanggal_laporan"]').value = new Date().toISOString().split('T')[0];

            // Handle status button clicks
            document.querySelectorAll('.monitoring-status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const assetIndex = this.dataset.asset;
                    const status = this.dataset.status;
                    const assetButtons = document.querySelectorAll(`[data-asset="${assetIndex}"]`);
                    const descGroup = document.getElementById(`desc-${assetIndex}`);
                    const statusInput = document.getElementById(`status-${assetIndex}`);
                    
                    // Remove active class from all buttons for this asset
                    assetButtons.forEach(button => {
                        button.classList.remove('active', 'baik', 'butuh-perawatan');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active', status.replace('_', '-'));
                    
                    // Set hidden input value
                    statusInput.value = status;
                    
                    // Show/hide description group
                    if (status === 'butuh_perawatan') {
                        descGroup.classList.add('show');
                    } else {
                        descGroup.classList.remove('show');
                    }
                });
            });

            // Handle file uploads
            document.querySelectorAll('.monitoring-file-input').forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    const assetIndex = this.id.split('-')[1];
                    const fileName = document.getElementById(`file-name-${assetIndex}`);
                    const preview = document.getElementById(`preview-${assetIndex}`);
                    
                    if (file) {
                        fileName.textContent = file.name;
                        
                        // Show image preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        fileName.textContent = '';
                        preview.style.display = 'none';
                    }
                });
            });

            // Form validation
            document.getElementById('monitoringForm').addEventListener('submit', function(e) {
                const namaPerlapor = document.querySelector('input[name="nama_pelapor"]');
                const statusInputs = document.querySelectorAll('input[name*="[status]"]');
                let hasError = false;
                let errorMessage = '';
                
                // Check if name is filled
                if (!namaPerlapor.value.trim()) {
                    hasError = true;
                    errorMessage = 'Nama pelapor harus diisi';
                } else {
                    // Check if all assets have status
                    statusInputs.forEach((input, index) => {
                        if (!input.value) {
                            hasError = true;
                            errorMessage = 'Silakan pilih status untuk semua aset';
                        }
                    });
                }
                
                if (hasError) {
                    e.preventDefault();
                    alert(errorMessage);
                    if (!namaPerlapor.value.trim()) {
                        namaPerlapor.focus();
                    }
                }
            });
        });
    </script>
</body>
</html>