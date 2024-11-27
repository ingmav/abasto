<?php

namespace App\Http\Controllers\API\Catalogos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \Validator,\Hash, \Response, \DB, \Storage, \File;
use Illuminate\Http\Response as HttpResponse;
use Carbon\Carbon;

use App\Models\Sistema\cpm;
use App\Models\Sistema\fecha_cpm;

class ClavesController extends Controller
{

    public function index(Request $request)
     {
         try{
            $parametros = $request->all();
            $fecha = Carbon::now();
            //$cpm = fecha_cpm::where("fecha","<=",$fecha->format("Y-m-d"))->orderBy("fecha", "desc")->first();
            $obj = cpm::with("catalogo_fecha")
                ->whereRaw("catalogo_fecha_cpm_id = (select id from catalogo_fecha_cpm where fecha<='".$fecha->format("Y-m-d")."' limit 1)");
            
             if(isset($parametros['query']) && $parametros['query']!='')
             {
                $obj = $obj->whereRaw("clave like '%".$parametros['query']."%' or descripcion like '%".$parametros['query']."%'");
             }
             if(isset($parametros['page'])){
                $obj = $obj->orderBy('updated_at','DESC');
                $resultadosPorPagina = isset($parametros["per_page"])? $parametros["per_page"] : 20;
                $obj = $obj->paginate($resultadosPorPagina);
            } else {
                $obj = $obj->get();
            }

             return response()->json($obj, HttpResponse::HTTP_OK);
         }catch(\Exception $e){
             DB::rollback();
             return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
         }
     }
    public function Validar(Request $request)
    {
        try{
            DB::beginTransaction();
            $parametros = $request->all();
            

            DB::statement("CREATE TEMPORARY TABLE __cpm (id int AUTO_INCREMENT PRIMARY KEY, catalogo_fecha_cpm_id BIGiNT, gpo VARCHAR(20), clave VARCHAR(18), descripcion VARCHAR(250), cpm MEDIUMINT UNSIGNED)");
            $origen = new fecha_cpm();
            $origen->fecha = Carbon::now();
            $origen->save();
            $bandera = 0;
            $registros = [];
            $contador = 0;
            $index = 0;
            for ($contador=0; $contador < count($parametros); $contador++) { 

                $registros[$index][$contador]['catalogo_fecha_cpm_id'] = $origen->id;
                $registros[$index][$contador]['gpo'] = $parametros[$contador]['GPO'];
                $registros[$index][$contador]['clave'] = $parametros[$contador]['CLAVE'];
                $registros[$index][$contador]['descripcion'] = $parametros[$contador]['DESCRIPCION'];
                $registros[$index][$contador]['cpm'] = $parametros[$contador]['CPM'];
                $bandera++;
                if($bandera == 1000)
                {
                    $index++;
                    $bandera=0;
                }

            }
            foreach ($registros as $key => $value) {
                DB::TABLE("__cpm")->insert($value);
            }
            
            //$contador = DB::statement("select count(*) from __cpm group by clave having count(*)>1");
            //DB::statement("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
            $validador = DB::TABLE("__cpm")
            ->select("clave", "descripcion", DB::RAW("count(*) as cantidad"))
            ->groupBy("clave")
            ->havingRaw("count(*) > 1")
            ->get();
            if(count($validador) > 0)
            {
                DB::rollback();
                return response()->json(["descripcion"=>"EXISTE UNA O MAS CLAVES DUPLICADAS", "listado"=>$validador], HttpResponse::HTTP_PRECONDITION_FAILED);
            }
            unset($registros);

            db::statement("insert into catalogo_cpm (catalogo_fecha_cpm_id, gpo, clave, descripcion, cpm) select catalogo_fecha_cpm_id, gpo, clave, descripcion, cpm from __cpm");
            DB::commit();
            return response()->json($validador, HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
        }
    }
}
