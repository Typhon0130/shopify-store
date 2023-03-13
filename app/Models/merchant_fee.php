<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class merchant_fee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'order_name', 'shop_price', 'rate', 'fee'/*, 'created', 'status'*/];

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}
