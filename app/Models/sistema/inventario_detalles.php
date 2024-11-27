<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class inventario_detalles extends Model
{
    use SoftDeletes;
    protected $table = 'inventario_detalles';

    public function inventario(){
        return $this->belongsTo('App\Models\sistema\inventario','inventario_id');
    }
    
    public function almacen(){
        return $this->belongsTo('App\Models\sistema\almacen','almacen_id');
    }
    
    public function cpm(){
        return $this->belongsTo('App\Models\sistema\cpm','catalogo_cpm_id');
    }
}
