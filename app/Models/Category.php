<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
