<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanan = Layanan::orderBy('nama_layanan')->get();
        return view('layanan.index', compact('layanan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:100|unique:layanan,nama_layanan',
            'harga'        => 'required|numeric|min:1000',
            'deskripsi'    => 'nullable|string|max:200',
        ], [
            'nama_layanan.unique' => 'Nama layanan sudah ada!',
            'harga.min'           => 'Harga minimal Rp 1.000',
        ]);

        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'harga'        => $request->harga,
            'deskripsi'    => $request->deskripsi,
            'is_active'    => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'layanan' => $layanan,
                'message' => 'Layanan berhasil ditambahkan!',
            ]);
        }

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $request->validate([
            'nama_layanan' => "required|string|max:100|unique:layanan,nama_layanan,{$id}",
            'harga'        => 'required|numeric|min:1000',
            'deskripsi'    => 'nullable|string|max:200',
        ]);

        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'harga'        => $request->harga,
            'deskripsi'    => $request->deskripsi,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'layanan' => $layanan,
                'message' => 'Layanan berhasil diperbarui!',
            ]);
        }

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil diperbarui!');
    }

    public function toggle(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->update(['is_active' => !$layanan->is_active]);

        $status = $layanan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $layanan->is_active,
                'message'   => "Layanan {$layanan->nama_layanan} berhasil {$status}!",
            ]);
        }

        return redirect()->route('layanan.index')->with('success', "Layanan {$layanan->nama_layanan} berhasil {$status}!");
    }

    public function destroy(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        // Check if layanan has pesanan
        if ($layanan->pesanan()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Layanan tidak dapat dihapus karena masih ada pesanan!'], 422);
            }
            return redirect()->back()->with('error', 'Layanan tidak dapat dihapus karena masih ada pesanan!');
        }

        $nama = $layanan->nama_layanan;
        $layanan->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Layanan {$nama} berhasil dihapus!"]);
        }

        return redirect()->route('layanan.index')->with('success', "Layanan {$nama} berhasil dihapus!");
    }

    public function getAll()
    {
        $layanan = Layanan::where('is_active', true)->orderBy('nama_layanan')->get();
        return response()->json($layanan);
    }
}
