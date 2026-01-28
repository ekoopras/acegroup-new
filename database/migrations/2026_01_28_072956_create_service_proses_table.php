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
        Schema::create('service_proses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('nama_barang');
            $table->string('nama_client');
            $table->string('nomor_wa');
            $table->string('nomor_surat')->unique();
            $table->longText('qrcode')->nullable();
            $table->date('tanggal_masuk');
            $table->text('kerusakan');
            $table->json('perlengkapan')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Proses', 'Pending'])->default('Proses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_proses');
    }
};
