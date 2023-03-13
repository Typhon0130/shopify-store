<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $table   = 'skus';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function sku()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function customSku(){
        return $this->hasMany(Customsku::class);
    }
    
}