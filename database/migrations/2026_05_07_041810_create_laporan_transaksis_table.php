<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_client_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelanggan');
            $table->string('nomor_surat')->unique();
            $table->string('nama_barang');
            $table->string('nomor_nota');
            $table->dateTime('tanggal');
            $table->decimal('total_bayar', 12, 2);
            $table->string('metode_bayar');
            $table->text('teknisi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_transaksis');
    }
};
