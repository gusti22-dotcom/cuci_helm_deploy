<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filterBulan = $request->get('bulan', now()->format('m'));
        $filterTahun = $request->get('tahun', now()->format('Y'));

        $laporanHarian = $this->queryHarian($filterBulan, $filterTahun);

        $totalBulanIni = $laporanHarian->sum('pendapatan');
        $totalPesanan  = $laporanHarian->sum('total_pesanan');
        $maxPendapatan = $laporanHarian->max('pendapatan') ?: 1;

        $laporanLayanan = $this->queryLayanan($filterBulan, $filterTahun);

        $years = Pesanan::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        // Pastikan tahun sekarang selalu ada di list
        $currentYear = now()->format('Y');
        if (!$years->contains($currentYear)) {
            $years = collect([$currentYear])->merge($years);
        }

        return view('laporan.index', compact(
            'laporanHarian', 'laporanLayanan',
            'filterBulan', 'filterTahun',
            'totalBulanIni', 'totalPesanan', 'maxPendapatan', 'years'
        ));
    }

    /**
     * API endpoint — mengembalikan SEMUA data laporan (harian + per layanan + stats)
     * untuk update real-time tanpa reload
     */
    public function getData(Request $request)
    {
        $filterBulan = $request->get('bulan', now()->format('m'));
        $filterTahun = $request->get('tahun', now()->format('Y'));

        $harian  = $this->queryHarian($filterBulan, $filterTahun);
        $layanan = $this->queryLayanan($filterBulan, $filterTahun);

        $totalPendapatan = $harian->sum('pendapatan');
        $totalPesanan    = $harian->sum('total_pesanan');
        $hariAktif       = $harian->count();
        $rataHari        = $hariAktif > 0 ? round($totalPendapatan / $hariAktif) : 0;
        $maxPendapatan   = $harian->max('pendapatan') ?: 1;

        return response()->json([
            // Stat summary
            'stats' => [
                'total_pendapatan'    => $totalPendapatan,
                'total_pendapatan_fmt'=> 'Rp ' . number_format($totalPendapatan, 0, ',', '.'),
                'total_pesanan'       => $totalPesanan,
                'hari_aktif'          => $hariAktif,
                'rata_hari'           => $rataHari,
                'rata_hari_fmt'       => 'Rp ' . number_format($rataHari, 0, ',', '.'),
            ],

            // Data chart bars harian
            'harian' => $harian->map(fn($item) => [
                'tanggal'        => \Carbon\Carbon::parse($item->tanggal)->format('d'),
                'tanggal_full'   => \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y'),
                'tanggal_label'  => \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM'),
                'total_pesanan'  => $item->total_pesanan,
                'selesai'        => $item->selesai,
                'proses'         => $item->proses,
                'pendapatan'     => (float) $item->pendapatan,
                'pendapatan_fmt' => 'Rp ' . number_format($item->pendapatan, 0, ',', '.'),
                'persen'         => $maxPendapatan > 0 ? round($item->pendapatan / $maxPendapatan * 100, 1) : 0,
            ]),

            // Data tabel per layanan
            'layanan' => $layanan->map(fn($item) => [
                'nama_layanan'       => $item->nama_layanan,
                'total_pesanan'      => $item->total_pesanan,
                'total_pendapatan'   => (float) $item->total_pendapatan,
                'total_pendapatan_fmt' => 'Rp ' . number_format($item->total_pendapatan, 0, ',', '.'),
            ]),
        ]);
    }

    // Private helpers

    private function queryHarian($bulan, $tahun)
    {
        return Pesanan::select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('COUNT(*) as total_pesanan'),
                DB::raw('SUM(CASE WHEN status = "selesai" THEN total_harga ELSE 0 END) as pendapatan'),
                DB::raw('SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai'),
                DB::raw('SUM(CASE WHEN status = "proses"  THEN 1 ELSE 0 END) as proses')
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('tanggal')
            ->get();
    }

    private function queryLayanan($bulan, $tahun)
    {
        return Pesanan::select(
                'nama_layanan',
                DB::raw('COUNT(*) as total_pesanan'),
                DB::raw('SUM(total_harga) as total_pendapatan')
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'selesai')
            ->groupBy('nama_layanan')
            ->orderByDesc('total_pendapatan')
            ->get();
    }
}
