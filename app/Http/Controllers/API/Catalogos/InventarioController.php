<?php

namespace App\Http\Controllers\API\Catalogos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \Validator,\Hash, \Response, \DB, \Storage, \File;
use Illuminate\Http\Response as HttpResponse;
use Carbon\Carbon;

use App\Models\Sistema\inventario;
use App\Models\Sistema\inventario_detalles;
use App\Models\Sistema\cpm;
use App\Models\Sistema\fecha_cpm;

class InventarioController extends Controller
{
    public function index($id, Request $request)
     {
         try{
            $parametros = $request->all();
             $obj = inventario_detalles::with("inventario", "almacen", "cpm")->where("inventario_id", $id);
            
             if(isset($parametros['query']) && $parametros['query']!='')
             {
                $obj = $obj->whereRaw("catalogo_cpm_id in (select id from catalogo_cpm where clave like '%".$parametros['query']."%' or descripcion like '%".$parametros['query']."%')");
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
    public function show(Request $request)
    {
        try{
            $parametros = $request->all();
            $fecha = $parametros['fecha_evaluacion'];
            $importaciones = inventario::where("fecha", $fecha)->get();
            
            return response()->json($importaciones, HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
        }
    }
    
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();
            $parametros = $request->all();
            $fecha = $parametros['fecha'];
            
            $datos = $parametros['data'];
            //DB::statement("drop table if exists __inventario");
            DB::statement("CREATE TEMPORARY TABLE __inventario (id int AUTO_INCREMENT PRIMARY KEY, almacen_id BIGiNT UNSIGNED, inventario_id BIGINT UNSIGNED,clave VARCHAR(18), catalogo_cpm_id BIGINT UNSIGNED default(0), lote VARCHAR(250), caducidad DATE, financiamiento VARCHAR(250), existencia MEDIUMINT UNSIGNED)");
            DB::statement("CREATE INDEX claves ON __inventario (clave)");
            $origen = new inventario();
            $origen->fecha = Carbon::now();
            $origen->save();
            $bandera = 0;
            $registros = [];
            $contador = 0;
            $index = 0;
            
            for ($contador=0; $contador < count($datos); $contador++) { 

                $caducidad = new carbon($datos[$contador]['CADUCIDAD']);
                $registros[$index][$contador]['almacen_id'] = $datos[$contador]['ALMACEN'];
                $registros[$index][$contador]['inventario_id'] = $origen->id;
                $registros[$index][$contador]['lote'] = $datos[$contador]['LOTE'];
                $registros[$index][$contador]['clave'] = $datos[$contador]['CLAVE'];
                $registros[$index][$contador]['caducidad'] = $caducidad->format("Y-m-d");
                $registros[$index][$contador]['financiamiento'] = $datos[$contador]['FINANCIAMIENTO'];
                $registros[$index][$contador]['existencia'] = $datos[$contador]['EXISTENCIAS'];
                $bandera++;
                if($bandera == 1000)
                {
                    $index++;
                    $bandera=0;
                }

            }
            foreach ($registros as $key => $value) {
                DB::TABLE("__inventario")->insert($value);
            }
            
            $fecha_actual = Carbon::now()->locale('es')->format("Y-m-d");
            
            /*obtencion de cpm actual */
            $cpm = fecha_cpm::where("fecha","<=",$fecha)->orderBy("fecha", "desc")->first();
            /*registro totales */
            $total                  = DB::TABLE("__inventario")->count();
            $caducados              = DB::TABLE("__inventario")->where("caducidad","<=",$fecha_actual)->count();
            $total_claves           = DB::TABLE("__inventario")->where("caducidad",">=",$fecha_actual)->select(DB::RAW("COUNT(DISTINCT(clave)) AS claves"))->first();
            $total_claves_cpm       = DB::TABLE("__inventario")
                                            ->where("caducidad",">=",$fecha_actual)
                                            ->whereRaw("CONVERT(clave USING utf8mb4) COLLATE utf8mb4_general_ci IN (SELECT clave FROM catalogo_cpm WHERE catalogo_fecha_cpm_id=$cpm->id)")
                                            ->select(DB::RAW("COUNT(DISTINCT(clave)) AS claves"))
                                            ->first();
            
            $total_insumos_cpm       = DB::TABLE("__inventario")
                                            ->where("caducidad",">=",$fecha_actual)
                                            ->whereRaw("CONVERT(clave USING utf8mb4) COLLATE utf8mb4_general_ci IN (SELECT clave FROM catalogo_cpm WHERE catalogo_fecha_cpm_id=$cpm->id)")
                                            ->count();
            
            //$origen = inventario::find(1);
            $origen->archivo                    = $parametros['nombre'];
            $origen->total_insumos              = $total;
            $origen->total_caducados            = $caducados;
            $origen->total_claves               = $total_claves->claves;
            $origen->total_claves_cpm           = $total_claves_cpm->claves;
            $origen->total_insumos_importados   = $total_insumos_cpm;
            $origen->save();
            /* Asignamos id de cpm a  */
            DB::statement("UPDATE __inventario a inner join catalogo_cpm b on CONVERT(a.clave USING utf8mb4) COLLATE utf8mb4_general_ci=b.clave set catalogo_cpm_id=b.id where b.catalogo_fecha_cpm_id=$cpm->id and a.caducidad>='$fecha_actual'");
            /*IMPORTAMOS LA BASE TEMPORAL A LA BASE PERMANENTE */
            DB::statement("insert into inventario_detalles (inventario_id, almacen_id, catalogo_cpm_id, lote, caducidad, financiamiento, existencia) select a.inventario_id, a.almacen_id, a.catalogo_cpm_id, a.lote, a.caducidad, a.financiamiento, a.existencia from __inventario a where a.catalogo_cpm_id!=0");
            
            
            $importaciones = inventario::where("fecha", $fecha)->get();
            DB::commit();
            return response()->json($importaciones, HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
        }
    }

    public function destroy($id)
    {
        try{
            $obj = inventario::find($id);
            $obj->detalles()->delete();
            $obj->delete();

            return response()->json(['data'=>'Registro eliminado'], HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['error'=>['message'=>$e->getMessage(),'line'=>$e->getLine()]], HttpResponse::HTTP_CONFLICT);
        }
    }
}
