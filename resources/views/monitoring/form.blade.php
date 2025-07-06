<!-- form.blade.php -->
@extends('layouts.mobile')

@section('content')
<style>
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
</style>

<div class="monitoring-container">
    <div class="monitoring-header">
        Monitoring
    </div>

    <form action="{{ route('pemantauan.monitoring.store') }}" method="POST" enctype="multipart/form-data" id="monitoringForm">
        @csrf
        <input type="hidden" name="kode_ruangan" value="{{ $kodeRuangan }}">

        <div class="monitoring-form-section">
            <div class="monitoring-form-group">
                <label>Nama Pelapor</label>
                <input type="text" name="nama_pelapor" class="monitoring-form-control" value="{{ auth()->user()->name }}" required>
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
                        <button type="button" class="monitoring-status-btn" data-status="baik" data-asset="{{ $index }}">Baik</button>
                        <button type="button" class="monitoring-status-btn" data-status="butuh_perawatan" data-asset="{{ $index }}">Butuh Perawatan</button>
                    </div>
                    
                    <div class="monitoring-description-group" id="desc-{{ $index }}">
                        <textarea name="asset_data[{{ $index }}][deskripsi]" class="monitoring-description-input" placeholder="Deskripsi kerusakan..."></textarea>
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
            <button type="submit" class="monitoring-btn monitoring-btn-save">Simpan</button>
            <button type="button" class="monitoring-btn monitoring-btn-cancel" onclick="window.history.back()">Hapus</button>
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
        const statusInputs = document.querySelectorAll('input[name*="[status]"]');
        let hasError = false;
        
        statusInputs.forEach(input => {
            if (!input.value) {
                hasError = true;
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Silakan pilih status untuk semua aset');
        }
    });
});
</script>
@endsection