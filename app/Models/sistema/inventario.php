<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class inventario extends Model
{
    use SoftDeletes;
    protected $table = 'inventario';

    public function detalles(){
        return $this->hasMany('App\Models\sistema\inventario_detalles','inventario_id');
    }

}
