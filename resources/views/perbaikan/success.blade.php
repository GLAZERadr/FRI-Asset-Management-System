<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Berhasil Dikirim</title>
    
    <!-- QR Scanner Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .success-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: successPulse 1.5s ease-in-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .success-subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .report-details {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #374151;
        }

        .detail-value {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .damage-id {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #9ca3af;
        }

        .btn-scan {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-scan:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        @media (min-width: 640px) {
            .action-buttons {
                flex-direction: row;
            }
        }

        /* QR Scanner Styles */
        .qr-scanner-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: flex;
            flex-direction: column;
        }

        .qr-scanner-header {
            background: rgba(0, 0, 0, 0.8);
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
            padding: 2rem;
        }

        #qr-video {
            width: 100%;
            height: auto;
            max-width: 400px;
            border-radius: 1rem;
        }

        .qr-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 2px solid transparent;
            border-radius: 1rem;
            pointer-events: none;
        }

        .qr-corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 4px solid #10b981;
        }

        .qr-corner.top-left {
            top: -4px;
            left: -4px;
            border-right: none;
            border-bottom: none;
            border-top-left-radius: 1rem;
        }

        .qr-corner.top-right {
            top: -4px;
            right: -4px;
            border-left: none;
            border-bottom: none;
            border-top-right-radius: 1rem;
        }

        .qr-corner.bottom-left {
            bottom: -4px;
            left: -4px;
            border-right: none;
            border-top: none;
            border-bottom-left-radius: 1rem;
        }

        .qr-corner.bottom-right {
            bottom: -4px;
            right: -4px;
            border-left: none;
            border-top: none;
            border-bottom-right-radius: 1rem;
        }

        .scanning-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: scan 2s linear infinite;
        }

        @keyframes scan {
            0% { transform: translateY(0); }
            100% { transform: translateY(280px); }
        }

        .qr-scanner-footer {
            background: rgba(0, 0, 0, 0.8);
            padding: 2rem 1rem;
            text-align: center;
            color: white;
        }

        .qr-result {
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            margin: 1rem;
            animation: slideInUp 0.3s ease-out;
        }

        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .scanner-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .scanner-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Success Icon -->
        <div class="success-icon">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <!-- Success Message -->
        <h1 class="success-title">Laporan Berhasil Dikirim!</h1>
        <p class="success-subtitle">
            Terima kasih telah melaporkan kerusakan aset. Tim maintenance akan segera menindaklanjuti laporan Anda.
        </p>

        <!-- Report Details -->
        <div class="report-details">
            <div class="detail-row">
                <span class="detail-label">ID Laporan:</span>
                <span class="damage-id">{{ $damagedAsset->damage_id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Asset ID:</span>
                <span class="detail-value">{{ $damagedAsset->asset_id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Nama Asset:</span>
                <span class="detail-value">{{ $damagedAsset->asset->nama_asset ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Lokasi:</span>
                <span class="detail-value">{{ $damagedAsset->asset->lokasi ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tanggal Laporan:</span>
                <span class="detail-value">{{ $damagedAsset->tanggal_pelaporan->format('d M Y') }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">            
            <a href="{{ route('login') }}" class="btn btn-secondary">
                Login Dashboard
            </a>
        </div>
    </div>
</body>
</html>