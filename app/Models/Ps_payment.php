<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ps_payment extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = ['id'];
}
