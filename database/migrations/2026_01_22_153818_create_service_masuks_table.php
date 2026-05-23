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
        Schema::create('service_masuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_client_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelanggan');
            $table->string('nama_barang');
            $table->string('nomor_surat')->unique();
            $table->longText('qrcode')->nullable();
            $table->date('tanggal_masuk');
            $table->json('kerusakan');
            $table->json('perlengkapan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_masuks');
    }
};
