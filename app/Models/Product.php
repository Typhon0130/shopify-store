<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function sku()
    {
        return $this->hasMany(Sku::class, 'product_id', 'id');
    }
    
}