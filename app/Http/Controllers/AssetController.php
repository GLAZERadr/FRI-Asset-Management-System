<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();
        
        // Apply filters if they exist
        if ($request->has('lokasi') && $request->lokasi) {
            $query->where('lokasi', $request->lokasi);
        }
        
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }
        
        $assets = $query->paginate(10);
        
        // Get unique locations and categories for filter dropdowns
        $locations = Asset::distinct()->pluck('lokasi');
        $categories = Asset::distinct()->pluck('kategori');
        
        return view('assets.index', compact('assets', 'locations', 'categories'));
    }
    
    public function show($id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        return view('assets.show', compact('asset'));
    }
    
    public function create()
    {
        return view('assets.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_asset' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'tingkat_kepentingan_asset' => 'nullable|string|max:255',
        ]);
        
        // Generate a unique asset ID
        $latestAsset = Asset::latest()->first();
        $assetNumber = $latestAsset ? intval(substr($latestAsset->asset_id, 4)) + 1 : 101;
        $assetId = '#AST' . $assetNumber;
        
        Asset::create([
            'asset_id' => $assetId,
            'nama_asset' => $validated['nama_asset'],
            'lokasi' => $validated['lokasi'],
            'kategori' => $validated['kategori'],
            'tingkat_kepentingan_asset' => $validated['tingkat_kepentingan_asset'],
        ]);
        
        return redirect()->route('pemantauan')
            ->with('success', 'Asset berhasil ditambahkan.');
    }
    
    public function edit($id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        return view('assets.edit', compact('asset'));
    }
    
    public function update(Request $request, $id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        
        $validated = $request->validate([
            'nama_asset' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'tingkat_kepentingan_asset' => 'nullable|string|max:255',
        ]);
        
        $asset->update([
            'nama_asset' => $validated['nama_asset'],
            'lokasi' => $validated['lokasi'],
            'kategori' => $validated['kategori'],
            'tingkat_kepentingan_asset' => $validated['tingkat_kepentingan_asset'],
        ]);
        
        return redirect()->route('pemantauan')
            ->with('success', 'Asset berhasil diperbarui.');
    }
}