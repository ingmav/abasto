<?php

namespace App\Models\sistema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class disposicion extends Model
{
    use SoftDeletes;
    protected $table = 'disposicion';

    public function detalles(){
        return $this->hasMany('App\Models\sistema\disposicion_detalles','disposicion_id');
    }
}
