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
        Schema::create('log_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_client_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelanggan');
            $table->string('nama_barang');
            $table->date('tanggal_pengambilan');
            $table->json('services')->nullable();
            $table->unsignedBigInteger('total_biaya')->default(0);
            $table->string('garansi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_services');
    }
};
