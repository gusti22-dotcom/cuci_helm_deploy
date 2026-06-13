<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_customer', 150);
            $table->unsignedBigInteger('layanan_id');
            $table->string('nama_layanan', 100);
            $table->integer('jumlah_helm');
            $table->decimal('harga_satuan', 10, 2);
            $table->decimal('total_harga', 10, 2);
            $table->enum('status', ['proses', 'selesai'])->default('proses');
            $table->date('tanggal')->nullable();
            $table->timestamps();

            $table->foreign('layanan_id')->references('id')->on('layanan')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
