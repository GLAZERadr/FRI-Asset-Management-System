<?php

namespace App\Http\Controllers;

use App\Models\DamagedAsset;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DamagedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = DamagedAsset::with('asset');
        
        // Apply filters if they exist
        if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
            $query->where('tingkat_kerusakan', $request->tingkat_kerusakan);
        }
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        // Staff logistik only sees their own reports
        if (Auth::user()->hasRole('staff_logistik')) {
            $query->where('pelapor', Auth::user()->division);
        }
        
        // Make sure to use paginate() to get a LengthAwarePaginator instance
        $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')->paginate(10);
        
        // Get locations for filter dropdown
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('damaged_assets.index', compact('damagedAssets', 'locations'));
    }
    
    public function show($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->with('asset')->firstOrFail();
        
        // Staff logistik can only view their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('damaged_assets.show', compact('damagedAsset'));
    }
    
    public function create()
    {
        $assets = Asset::all();
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('damaged_assets.create', compact('assets', 'locations'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,asset_id',
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'deskripsi_kerusakan' => 'required|string',
            'vendor' => 'nullable|string',
        ]);
        
        // Generate a unique damage ID
        $latestDamage = DamagedAsset::latest()->first();
        $damageNumber = $latestDamage ? intval(substr($latestDamage->damage_id, 4)) + 1 : 1;
        $damageId = 'DMG-' . str_pad($damageNumber, 5, '0', STR_PAD_LEFT);
        
        $damagedAsset = DamagedAsset::create([
            'damage_id' => $damageId,
            'asset_id' => $validated['asset_id'],
            'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
            'estimasi_biaya' => $validated['estimasi_biaya'],
            'deskripsi_kerusakan' => $validated['deskripsi_kerusakan'],
            'tanggal_pelaporan' => now(),
            'pelapor' => Auth::user()->division,
            'vendor' => $validated['vendor'] ?? null,
        ]);
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil dilaporkan.');
    }
    
    public function edit($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->with('asset')->firstOrFail();
        
        // Staff logistik can only edit their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $assets = Asset::all();
        
        return view('damaged_assets.edit', compact('damagedAsset', 'assets'));
    }
    
    public function update(Request $request, $id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->firstOrFail();
        
        // Staff logistik can only update their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'deskripsi_kerusakan' => 'required|string',
            'vendor' => 'nullable|string',
        ]);
        
        $damagedAsset->update([
            'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
            'estimasi_biaya' => $validated['estimasi_biaya'],
            'deskripsi_kerusakan' => $validated['deskripsi_kerusakan'],
            'vendor' => $validated['vendor'] ?? null,
        ]);
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->firstOrFail();
        
        // Staff logistik can only delete their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $damagedAsset->delete();
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil dihapus.');
    }
}