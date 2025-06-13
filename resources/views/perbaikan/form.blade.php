<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Kerusakan Aset</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .monitoring-container {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }

        .monitoring-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
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

        /* Photo upload styles */
        .monitoring-photo-upload-section {
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .monitoring-photo-upload-section:hover {
            border-color: #3498db;
            background: #f0f8ff;
        }

        .monitoring-upload-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
            margin-bottom: 10px;
        }

        .monitoring-upload-btn:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .monitoring-file-input {
            display: none;
        }

        .monitoring-photo-preview {
            margin-top: 15px;
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            display: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .monitoring-selected-file {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
        }

        .monitoring-upload-instructions {
            color: #888;
            font-size: 14px;
            margin-top: 10px;
        }

        .monitoring-camera-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
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

        .monitoring-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }

        /* Form field styles */
        .monitoring-form-group.required label::after {
            content: " *";
            color: #e74c3c;
        }

        .monitoring-description-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .monitoring-description-textarea:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.1);
        }

        .form-control select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            cursor: pointer;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-button:hover {
            color: #333;
        }

        .back-button svg {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="monitoring-container">
        <div class="monitoring-header">
            Laporan Kerusakan Aset
        </div>

        <form action="{{ route('damage-report.store') }}" method="POST" enctype="multipart/form-data" id="monitoringForm">
            @csrf

            <div class="monitoring-form-section">
                <!-- Back button -->
                <a href="{{ route('login') }}" class="back-button">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Kembali ke Login
                </a>

                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Photo Upload Section - FIRST -->
                <div class="monitoring-form-group required">
                    <label>Upload Foto Kerusakan</label>
                    <div class="monitoring-photo-upload-section">
                        <div class="monitoring-camera-icon">📷</div>
                        <label for="foto-kerusakan" class="monitoring-upload-btn">
                            Pilih Foto
                        </label>
                        <input type="file" 
                               id="foto-kerusakan" 
                               name="foto_kerusakan" 
                               class="monitoring-file-input" 
                               accept="image/*"
                               capture="environment"
                               required>
                        <div class="monitoring-upload-instructions">
                            Ambil foto yang jelas menunjukkan kerusakan pada aset
                        </div>
                        <div id="file-name" class="monitoring-selected-file"></div>
                        <img id="photo-preview" class="monitoring-photo-preview" alt="Preview">
                    </div>
                </div>

                <!-- Asset Information -->
                <div class="monitoring-form-group required">
                    <label>ID Aset</label>
                    <input type="text" 
                           name="asset_id" 
                           class="monitoring-form-control" 
                           value="{{ $assetData['asset_id'] ?? request('asset_id') ?? '' }}" 
                           readonly
                           required>
                </div>

                <div class="monitoring-form-group required">
                    <label>Nama Aset</label>
                    <input type="text" 
                           name="nama_aset" 
                           class="monitoring-form-control" 
                           value="{{ $assetData['nama_asset'] ?? '' }}" 
                           readonly
                           required>
                </div>

                <div class="monitoring-form-group required">
                    <label>Lokasi Aset</label>
                    <input type="text" 
                           name="lokasi" 
                           class="monitoring-form-control" 
                           value="{{ $assetData['lokasi'] ?? '' }}" 
                           readonly
                           required>
                </div>

                <div class="monitoring-form-group required">
                    <label>Tanggal Laporan</label>
                    <input type="date" 
                           name="tanggal_laporan" 
                           class="monitoring-form-control" 
                           value="{{ date('Y-m-d') }}" 
                           required>
                </div>

                <div class="monitoring-form-group required">
                    <label>Role Pelapor</label>
                    <select name="role_pelapor" class="monitoring-form-control form-control" required>
                        <option value="">Pilih Role Pelapor</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <!-- Description Section -->
                <div class="monitoring-form-group required">
                    <label>Deskripsi Kerusakan</label>
                    <textarea name="deskripsi_kerusakan" 
                              class="monitoring-description-textarea" 
                              placeholder="Jelaskan kondisi kerusakan aset secara detail..."
                              required></textarea>
                    <div id="char-counter" style="font-size: 12px; color: #666; text-align: right; margin-top: 5px;">
                        0 karakter
                    </div>
                </div>
            </div>

            <div class="monitoring-action-buttons">
                <button type="submit" class="monitoring-btn monitoring-btn-save" id="submitBtn">
                    Simpan Laporan
                </button>
                <button type="button" class="monitoring-btn monitoring-btn-cancel" onclick="window.history.back()">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('foto-kerusakan');
        const fileName = document.getElementById('file-name');
        const preview = document.getElementById('photo-preview');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('monitoringForm');

        // Set today's date
        document.querySelector('input[name="tanggal_laporan"]').value = new Date().toISOString().split('T')[0];

        // Handle file upload
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Silakan pilih file gambar yang valid');
                    this.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB');
                    this.value = '';
                    return;
                }

                fileName.textContent = `File terpilih: ${file.name}`;
                
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

        // Form validation
        form.addEventListener('submit', function(e) {
            const assetId = document.querySelector('input[name="asset_id"]').value.trim();
            const namaAset = document.querySelector('input[name="nama_aset"]').value.trim();
            const lokasi = document.querySelector('input[name="lokasi"]').value.trim();
            const tanggalLaporan = document.querySelector('input[name="tanggal_laporan"]').value;
            const rolePelapor = document.querySelector('select[name="role_pelapor"]').value;
            const deskripsi = document.querySelector('textarea[name="deskripsi_kerusakan"]').value.trim();
            const foto = fileInput.files[0];

            let errors = [];

            // Validate required fields
            if (!assetId) errors.push('ID Aset harus diisi');
            if (!namaAset) errors.push('Nama Aset harus diisi');
            if (!lokasi) errors.push('Lokasi Aset harus diisi');
            if (!tanggalLaporan) errors.push('Tanggal Laporan harus diisi');
            if (!rolePelapor) errors.push('Role Pelapor harus dipilih');
            if (!deskripsi) errors.push('Deskripsi Kerusakan harus diisi');
            if (!foto) errors.push('Foto kerusakan harus diupload');

            // Validate description length
            if (deskripsi && deskripsi.length < 10) {
                errors.push('Deskripsi kerusakan minimal 10 karakter');
            }

            // Validate date (not future date)
            const today = new Date().toISOString().split('T')[0];
            if (tanggalLaporan > today) {
                errors.push('Tanggal laporan tidak boleh di masa depan');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Kesalahan:\n' + errors.join('\n'));
                return false;
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
        });

        // Real-time character counter for description
        const descriptionTextarea = document.querySelector('textarea[name="deskripsi_kerusakan"]');
        const charCounter = document.getElementById('char-counter');

        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} karakter`;
            
            if (length < 10) {
                charCounter.style.color = '#e74c3c';
            } else {
                charCounter.style.color = '#27ae60';
            }
        });

        // Auto-resize textarea
        descriptionTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(100, this.scrollHeight) + 'px';
        });

        // Check if asset data is missing
        const assetId = document.querySelector('input[name="asset_id"]').value;
        if (!assetId) {
            alert('Data asset tidak ditemukan. Silakan scan QR code terlebih dahulu.');
            window.location.href = '{{ route("login") }}';
        }
    });
    </script>
</body>
</html>