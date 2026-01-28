<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProses extends Model
{
    protected $table = 'service_proses';

    protected $fillable = [
        'category_id',
        'nama_barang',
        'nama_client',
        'nomor_wa',
        'nomor_surat',
        'qrcode',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'perlengkapan' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
