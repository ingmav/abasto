<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class requerimiento extends Model
{
    use SoftDeletes;
    protected $table = 'unidad_requerimiento';
}
