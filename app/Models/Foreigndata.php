<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foreigndata extends Model
{
    use HasFactory;
    protected $table   = 'foreign_products';
    protected $guarded = ['id'];
    public $timestamps = false;
}
