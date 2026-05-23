<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceJadi extends Model
{
    protected $fillable = [
        'category_id',
        'data_client_id',
        'nama_pelanggan',
        'nama_barang',
        'nomor_surat',
        'qrcode',
        'tanggal_masuk',
        'tanggal_selesai',
        'garansi',
        'services',
        'potongan_biaya',
        'total_biaya',
        'log_status',
        'token',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_selesai' => 'date',
        'potongan_biaya' => 'string',
        'services' => 'array',
        'log_status' => 'array',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function dataClient()
    {
        return $this->belongsTo(DataClient::class);
    }
}
