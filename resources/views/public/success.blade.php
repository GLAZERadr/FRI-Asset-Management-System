<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Berhasil Dikirim</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .success-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: bounce 0.6s ease-out 0.2s;
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -10px, 0);
            }
            70% {
                transform: translate3d(0, -5px, 0);
            }
            90% {
                transform: translate3d(0, -2px, 0);
            }
        }

        .report-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-weight: 500;
            color: #666;
            font-size: 14px;
        }

        .summary-value {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .asset-list {
            margin-top: 1rem;
        }

        .asset-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: white;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 4px solid #10b981;
        }

        .asset-item.butuh-perawatan {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        .asset-info {
            flex: 1;
        }

        .asset-id {
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        .asset-name {
            font-size: 12px;
            color: #666;
        }

        .asset-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-baik {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-butuh-perawatan {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .reference-id {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .reference-id-label {
            font-size: 12px;
            color: #3b82f6;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .reference-id-value {
            font-size: 18px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 2px;
        }

        .next-steps {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: left;
        }

        .next-steps h4 {
            color: #d97706;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }

        .next-steps ul {
            color: #92400e;
            font-size: 13px;
            margin: 0;
            padding-left: 1rem;
        }

        .next-steps li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <!-- Success Icon -->
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Success Message -->
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Laporan Berhasil Dikirim!</h1>
            <p class="text-gray-600 mb-4">Terima kasih atas kontribusi Anda dalam monitoring aset.</p>

            <!-- Reference ID -->
            <div class="reference-id">
                <div class="reference-id-label">ID Laporan</div>
                <div class="reference-id-value">#{{ str_pad($laporan->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>

            <!-- Report Summary -->
            <div class="report-summary">
                <h3 class="font-semibold text-gray-800 mb-3">Ringkasan Laporan</h3>
                
                <div class="summary-row">
                    <span class="summary-label">Ruangan</span>
                    <span class="summary-value">{{ $laporan->kode_ruangan }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Pelapor</span>
                    <span class="summary-value">{{ $laporan->nama_pelapor }}</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tanggal</span>
                    <span class="summary-value">{{ \Carbon\Carbon::parse($laporan->tanggal_laporan)->format('d/m/Y') }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="/" class="btn btn-primary">
                    🏠 Kembali ke Beranda
                </a>
                
                <a href="/public" class="btn btn-secondary">
                    📱 Scan QR Code Lain
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                <p class="text-xs text-gray-500">
                    Laporan ini dibuat pada {{ \Carbon\Carbon::parse($laporan->created_at)->format('d/m/Y H:i') }}
                </p>
                @if($laporan->email_pelapor || $laporan->telepon_pelapor)
                <p class="text-xs text-gray-500 mt-1">
                    Tim akan menghubungi Anda jika diperlukan informasi tambahan
                </p>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Link laporan berhasil disalin!');
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(text);
                });
            } else {
                fallbackCopyTextToClipboard(text);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            
            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    alert('Link laporan berhasil disalin!');
                } else {
                    alert('Gagal menyalin link. Silakan salin manual: ' + text);
                }
            } catch (err) {
                console.error('Fallback: Could not copy text: ', err);
                alert('Gagal menyalin link. Silakan salin manual: ' + text);
            }

            document.body.removeChild(textArea);
        }

        // Auto-redirect after 30 seconds (optional)
        setTimeout(function() {
            if (confirm('Apakah Anda ingin kembali ke halaman utama?')) {
                window.location.href = '/';
            }
        }, 30000);
    </script>
</body>
</html>