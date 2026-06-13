<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Gunakan now() — dengan timezone Asia/Jakarta dari config/app.php
        $today = now()->toDateString();

        $totalPesananHariIni    = Pesanan::whereDate('tanggal', $today)->count();
        $totalPendapatanHariIni = Pesanan::whereDate('tanggal', $today)->where('status', 'selesai')->sum('total_harga');
        $totalProses            = Pesanan::where('status', 'proses')->count();
        $totalSelesai           = Pesanan::whereDate('tanggal', $today)->where('status', 'selesai')->count();

        $layanan = Layanan::where('is_active', true)->orderBy('nama_layanan')->get();

        $filterLayanan = $request->get('filter_layanan', '');
        $filterNama    = $request->get('filter_nama', '');

        $query = Pesanan::whereDate('tanggal', $today)->with('layanan')->orderByDesc('created_at');
        if ($filterLayanan) $query->where('layanan_id', $filterLayanan);
        if ($filterNama)    $query->where('nama_customer', 'LIKE', "%{$filterNama}%");
        $pesananHariIni = $query->get();

        // Cookie: simpan waktu kunjungan dalam WIB
        $lastVisit = $request->cookie('last_visit', null);

        $response = response()->view('dashboard.index', compact(
            'totalPesananHariIni', 'totalPendapatanHariIni',
            'totalProses', 'totalSelesai',
            'layanan', 'pesananHariIni',
            'filterLayanan', 'filterNama', 'lastVisit'
        ));

        // Simpan waktu kunjungan sekarang (WIB) ke cookie
        return $response->cookie('last_visit', now()->format('d M Y, H:i') . ' WIB', 60 * 24 * 30);
    }

    /**
     * API: stats realtime untuk update dashboard tanpa reload
     */
    public function getStats()
    {
        $today = now()->toDateString();

        return response()->json([
            'total_pesanan'              => Pesanan::whereDate('tanggal', $today)->count(),
            'total_pendapatan'           => Pesanan::whereDate('tanggal', $today)->where('status', 'selesai')->sum('total_harga'),
            'total_pendapatan_formatted' => 'Rp ' . number_format(
                Pesanan::whereDate('tanggal', $today)->where('status', 'selesai')->sum('total_harga'),
                0, ',', '.'
            ),
            'total_proses'               => Pesanan::where('status', 'proses')->count(),
            'total_selesai'              => Pesanan::whereDate('tanggal', $today)->where('status', 'selesai')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_customer' => 'required|string|max:150',
            'layanan_id'    => 'required|exists:layanan,id',
            'jumlah_helm'   => 'required|integer|min:1|max:50',
        ]);

        $layanan    = Layanan::findOrFail($request->layanan_id);
        $totalHarga = $layanan->harga * $request->jumlah_helm;

        $pesanan = Pesanan::create([
            'nama_customer' => $request->nama_customer,
            'layanan_id'    => $layanan->id,
            'nama_layanan'  => $layanan->nama_layanan,
            'jumlah_helm'   => $request->jumlah_helm,
            'harga_satuan'  => $layanan->harga,
            'total_harga'   => $totalHarga,
            'status'        => 'proses',
            'tanggal'       => now()->toDateString(), // tanggal WIB
        ]);

        session()->flash('success', "Pesanan untuk {$request->nama_customer} berhasil disimpan!");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil disimpan!',
                'pesanan' => [
                    'id'            => $pesanan->id,
                    'nama_customer' => $pesanan->nama_customer,
                    'nama_layanan'  => $pesanan->nama_layanan,
                    'jumlah_helm'   => $pesanan->jumlah_helm,
                    'total_harga'   => $pesanan->total_harga,
                    'status'        => $pesanan->status,
                    // Waktu dalam WIB
                    'created_at'    => now()->format('H:i'),
                ],
            ]);
        }
        return redirect()->route('dashboard');
    }

    public function updateStatus(Request $request, $id)
    {
        $pesanan         = Pesanan::findOrFail($id);
        $pesanan->status = $pesanan->status === 'proses' ? 'selesai' : 'proses';
        $pesanan->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $pesanan->status,
                'badge'   => $pesanan->status_badge,
            ]);
        }
        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    public function countProses()
    {
        return response()->json(['count' => Pesanan::where('status', 'proses')->count()]);
    }
}
