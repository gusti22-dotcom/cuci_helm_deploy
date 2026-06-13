<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $customers = ['Budi Santoso', 'Siti Rahayu', 'Dimas Pratama', 'Rini Wulandari', 'Eko Saputro',
                      'Dewi Cahya', 'Hendra Gunawan', 'Maya Sari', 'Agus Wijaya', 'Lia Permata'];

        $layanan = DB::table('layanan')->get();
        if ($layanan->isEmpty()) return;

        $statuses = ['proses', 'selesai', 'selesai', 'selesai'];

        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day)->format('Y-m-d');
            $count = rand(3, 8);
            for ($i = 0; $i < $count; $i++) {
                $l = $layanan->random();
                $jumlah = rand(1, 4);
                $total = $l->harga * $jumlah;
                DB::table('pesanan')->insert([
                    'nama_customer' => $customers[array_rand($customers)],
                    'layanan_id'    => $l->id,
                    'nama_layanan'  => $l->nama_layanan,
                    'jumlah_helm'   => $jumlah,
                    'harga_satuan'  => $l->harga,
                    'total_harga'   => $total,
                    'status'        => $statuses[array_rand($statuses)],
                    'tanggal'       => $date,
                    'created_at'    => $date . ' ' . sprintf('%02d:%02d:00', rand(8, 20), rand(0, 59)),
                    'updated_at'    => $date . ' ' . sprintf('%02d:%02d:00', rand(8, 20), rand(0, 59)),
                ]);
            }
        }
    }
}
