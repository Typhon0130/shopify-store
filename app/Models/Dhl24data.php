<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhl24data extends Model
{
   use HasFactory;

   protected $guarded = ['id'];
   protected $table = 'dhl24data';

   public function parent()
   {
      return $this->belongsTo(Order::class);
   }
}
