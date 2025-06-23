<!DOCTYPE html>
<html>
<head>
    <title>Monitoring Test - {{ $kodeRuangan }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-info { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .asset-item { background: #e8f5e8; padding: 10px; margin: 5px 0; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Monitoring Form Debug</h1>
    
    <div class="debug-info">
        <h3>Debug Information:</h3>
        <p><strong>Room Code:</strong> {{ $kodeRuangan }}</p>
        <p><strong>Asset Count:</strong> {{ $assets->count() }}</p>
        <p><strong>Current URL:</strong> {{ request()->url() }}</p>
        <p><strong>Route Name:</strong> {{ request()->route()->getName() }}</p>
    </div>
    
    @if($assets->count() > 0)
        <div class="debug-info">
            <h3>Assets Found:</h3>
            @foreach($assets as $asset)
                <div class="asset-item">
                    <strong>{{ $asset->asset_id }}</strong> - {{ $asset->nama_asset }}
                    <br><small>Room: {{ $asset->kode_ruangan }}</small>
                </div>
            @endforeach
        </div>
        
        <p><a href="{{ route('public.monitoring.form', ['kodeRuangan' => $kodeRuangan]) }}">Reload this page</a></p>
        <p><a href="{{ route('public.index') }}">Back to Scanner</a></p>
    @else
        <div class="debug-info">
            <h3>No Assets Found</h3>
            <p>No assets found for room code: {{ $kodeRuangan }}</p>
        </div>
    @endif
</body>
</html>