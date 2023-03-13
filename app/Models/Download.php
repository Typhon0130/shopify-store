<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = ["order_id"];

    public function order()
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }
}