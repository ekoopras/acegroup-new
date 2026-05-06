<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kerusakan extends Model
{
    protected $fillable = [
        'category_id',
        'nama_kerusakan',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
