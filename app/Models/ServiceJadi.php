<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceJadi extends Model
{
    protected $fillable = [
        'category_id',
        'nama_barang',
        'nama_client',
        'nomor_wa',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
        'nomor_surat',
        'qrcode',
        'tanggal_selesai',
        'jasa_service',
        'biaya',
        'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
