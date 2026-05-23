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
        Schema::create('service_jadis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_client_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelanggan');
            $table->string('nama_barang');
            $table->string('nomor_surat')->unique();
            $table->longText('qrcode')->nullable();
            $table->date('tanggal_masuk');
            $table->string('garansi')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->json('services')->nullable();
            $table->unsignedBigInteger('total_biaya')->default(0);
            $table->string('token')->nullable();
            $table->json('log_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_jadis');
    }
};

$table->foreignId('category_id')->constrained()->cascadeOnDelete();
