<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customsku extends Model
{
    use HasFactory;
    protected $table   = 'custom_sku';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function sku(){
        return $this->belongsTo(\App\Models\Sku::class);
    }
}
