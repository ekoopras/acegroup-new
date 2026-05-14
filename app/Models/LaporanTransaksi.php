<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanTransaksi extends Model
{
    protected $fillable = [
        'category_id',
        'data_client_id',
        'nomor_surat',
        'nama_barang',
        'nomor_nota',
        'tanggal',
        'total_bayar',
        'metode_bayar',
        'teknisi',
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
