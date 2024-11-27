<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class cpm extends Model
{
    use SoftDeletes;
    protected $table = 'catalogo_cpm';

    public function disposicion(){
        return $this->hasMany('App\Models\sistema\disposicion_detalles','catalogo_cpm_id')->orderBy("caducidad", "desc");
    }

    public function catalogo_fecha(){
        return $this->belongsTo('App\Models\sistema\fecha_cpm','catalogo_fecha_cpm_id');
    }
}
