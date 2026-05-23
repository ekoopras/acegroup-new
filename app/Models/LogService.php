<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogService extends Model
{
    protected $fillable = [
        'category_id',
        'data_client_id',
        'nama_pelanggan',
        'nama_barang',
        'tanggal_pengambilan',
        'services',
        'total_biaya',
        'garansi',
    ];

    protected $casts = [
        'services' => 'array',
        'tanggal_pengambilan' => 'date',
    ];

    public function dataClient()
    {
        return $this->belongsTo(DataClient::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
