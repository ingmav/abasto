<?php

namespace App\Http\Controllers\API\Sistema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \Validator,\Hash, \Response, \DB, \Storage, \File;
use Illuminate\Http\Response as HttpResponse;
use Carbon\Carbon;

use App\Models\Sistema\fecha_cpm;
use App\Models\Sistema\cpm;

class ReportesController extends Controller
{
    public function abasto(Request $request)
    {
        try{
            DB::beginTransaction();
            $parametros = $request->all();
            $origen = new Carbon($parametros['fecha']);
            $cpm_origen = fecha_cpm::where("fecha","<=",$origen)->orderBy("fecha", "desc")->first();

             $cpm = cpm::with((['disposicion' => function($q) use ($parametros) {
                        $q->whereRaw("disposicion_id in (select id from disposicion where fecha='".$parametros['fecha']."' and deleted_at is null)");
                    }]))
                    ->where("catalogo_fecha_cpm_id", $cpm_origen->id)
                    ->select(
                        "catalogo_cpm.id",
                        "catalogo_cpm.clave", 
                        "catalogo_cpm.descripcion", 
                        "catalogo_cpm.cpm", 
                        DB::RAW("IF(SUM(inventario_detalles.existencia) IS NULL,0,SUM(inventario_detalles.`existencia`)) AS existencia_inventario"),
                        DB::RAW(" IF(SUM(disposicion_detalles.existencia) IS NULL,0,SUM(disposicion_detalles.`existencia`)) AS existencia_disposicion")
                        )
                    ->leftJoin("inventario_detalles", function($join) use ($parametros)
                    {
                        $join->on('inventario_detalles.catalogo_cpm_id', 'catalogo_cpm.id');
                        $join->whereRaw("inventario_detalles.inventario_id in (select id from inventario where fecha='".$parametros['fecha']."' and deleted_at is null)");
                      
                    })
                    ->leftJoin("disposicion_detalles", function($join) use ($parametros)
                    {
                        $join->on('disposicion_detalles.catalogo_cpm_id', '=', 'catalogo_cpm.id');
                        $join->whereRaw("disposicion_detalles.disposicion_id in (select id from disposicion where fecha='".$parametros['fecha']."' and deleted_at is null)");
                      
                    })
                    //->where("catalogo_cpm.clave", "010.000.0254.00")
                    ->whereNotNull("disposicion_detalles.existencia")
            ->groupBy("catalogo_cpm.id");

            
            $cpm = $cpm->get();
            DB::commit();
            return response()->json($cpm, HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
        }
    }
}
