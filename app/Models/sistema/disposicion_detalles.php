<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class disposicion_detalles extends Model
{
    use SoftDeletes;
    protected $table = 'disposicion_detalles';

    public function disposicion(){
        return $this->belongsTo('App\Models\sistema\disposicion','disposicion_id');
    }
    
    
    public function cpm(){
        return $this->belongsTo('App\Models\sistema\cpm','catalogo_cpm_id');
    }
}
