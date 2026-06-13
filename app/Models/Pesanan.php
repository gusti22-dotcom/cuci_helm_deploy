<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $fillable = [
        'nama_customer',
        'layanan_id',
        'nama_layanan',
        'jumlah_helm',
        'harga_satuan',
        'total_harga',
        'status',
        'tanggal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'total_harga'  => 'decimal:2',
        'tanggal'      => 'date',
    ];

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'selesai'
            ? '<span class="badge badge-success">Selesai</span>'
            : '<span class="badge badge-warning">Proses</span>';
    }
}
