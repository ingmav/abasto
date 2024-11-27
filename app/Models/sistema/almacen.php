<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class almacen extends Model
{
    use SoftDeletes;
    protected $table = 'almacen';
}
