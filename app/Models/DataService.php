<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataService extends Model
{
    protected $fillable = [
        'category_id',
        'data_client_id',
        'nama_pelanggan',
        'nama_barang',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
    ];

    protected $casts = [
        'kerusakan' => 'array',
        'perlengkapan' => 'array',
        'tanggal_masuk' => 'date',
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
