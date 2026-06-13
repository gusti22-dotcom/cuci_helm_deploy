<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $layanan = [
            ['nama_layanan' => 'Cuci Biasa', 'harga' => 15000, 'deskripsi' => 'Cuci helm standar luar dan dalam', 'is_active' => true],
            ['nama_layanan' => 'Cuci Premium', 'harga' => 25000, 'deskripsi' => 'Cuci plus wax dan poles batok', 'is_active' => true],
            ['nama_layanan' => 'Cuci Ekspres', 'harga' => 30000, 'deskripsi' => 'Cuci kilat 30 menit selesai', 'is_active' => true],
            ['nama_layanan' => 'Cuci Interior', 'harga' => 20000, 'deskripsi' => 'Fokus busa dan batok dalam', 'is_active' => true],
            ['nama_layanan' => 'Full Detailing', 'harga' => 50000, 'deskripsi' => 'Cuci total, poles, wax, parfum', 'is_active' => true],
        ];

        foreach ($layanan as $item) {
            DB::table('layanan')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
