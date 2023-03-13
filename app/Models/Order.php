<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function files()
    {
        return $this->hasMany(OrderData::class, 'order_id', 'id');
    }

    public function dhl24data()
    {
        return $this->hasOne(Dhl24data::class);
    }
}
