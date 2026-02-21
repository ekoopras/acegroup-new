<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProses extends Model
{
    protected $table = 'service_proses';

    protected $fillable = [
        'category_id',
        'data_client_id',
        'nama_barang',
        'nomor_surat',
        'qrcode',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
        'status',
        'user_id',
    ];

    protected $casts = [
        'perlengkapan' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function dataClient()
    {
        return $this->belongsTo(DataClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
