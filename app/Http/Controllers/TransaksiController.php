<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $filterTanggal = $request->get('tanggal', '');
        $filterBulan   = $request->get('bulan', '');
        $filterTahun   = $request->get('tahun', date('Y'));

        $query = Pesanan::with('layanan')->orderByDesc('created_at');

        if ($filterTanggal) {
            $query->whereDay('tanggal', $filterTanggal);
        }
        if ($filterBulan) {
            $query->whereMonth('tanggal', $filterBulan);
        }
        if ($filterTahun) {
            $query->whereYear('tanggal', $filterTahun);
        }

        $transaksi = $query->get();

        $totalPendapatan = $transaksi->where('status', 'selesai')->sum('total_harga');
        $totalPesanan    = $transaksi->count();

        $years = Pesanan::selectRaw("strftime('%Y', tanggal) as tahun")
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        return view('transaksi.index', compact(
            'transaksi', 'filterTanggal', 'filterBulan', 'filterTahun',
            'totalPendapatan', 'totalPesanan', 'years'
        ));
    }

    public function getData(Request $request)
    {
        $filterTanggal = $request->get('tanggal', '');
        $filterBulan   = $request->get('bulan', '');
        $filterTahun   = $request->get('tahun', date('Y'));

        $query = Pesanan::with('layanan')->orderByDesc('created_at');

        if ($filterTanggal) {
            $query->whereDay('tanggal', $filterTanggal);
        }
        if ($filterBulan) {
            $query->whereMonth('tanggal', $filterBulan);
        }
        if ($filterTahun) {
            $query->whereYear('tanggal', $filterTahun);
        }

        $transaksi = $query->get()->map(function ($item) {
            return [
                'id'            => $item->id,
                'nama_customer' => $item->nama_customer,
                'nama_layanan'  => $item->nama_layanan,
                'jumlah_helm'   => $item->jumlah_helm,
                'total_harga'   => $item->total_harga,
                'total_formatted' => 'Rp ' . number_format($item->total_harga, 0, ',', '.'),
                'status'        => $item->status,
                'tanggal'       => $item->tanggal->format('d/m/Y'),
                'created_at'    => $item->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data'             => $transaksi,
            'total_pendapatan' => 'Rp ' . number_format($transaksi->where('status', 'selesai')->sum('total_harga'), 0, ',', '.'),
            'total_pesanan'    => $transaksi->count(),
        ]);
    }
}
