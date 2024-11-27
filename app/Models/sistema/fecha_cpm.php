<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class fecha_cpm extends Model
{
    use SoftDeletes;
    protected $table = 'catalogo_fecha_cpm';
}
