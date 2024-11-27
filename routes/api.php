<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Catalogos\ClavesController;
use App\Http\Controllers\API\Catalogos\InventarioController;
use App\Http\Controllers\API\Catalogos\DisposicionController;
use App\Http\Controllers\API\Sistema\ReportesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::post('claves',                           'API\Catalogos\ClavesController@Validar');

Route::controller(InventarioController::class)->group(function() {
    Route::get('/inventario/{id}', 'index');
    Route::post('/inventario', 'store');
    Route::delete('/inventario/{id}', 'destroy');
    Route::get('/inventario', 'show');
});

Route::controller(DisposicionController::class)->group(function() {
    Route::get('/disposicion/{id}', 'index');
    Route::post('/disposicion', 'store');
    Route::delete('/disposicion/{id}', 'destroy');
    Route::get('/disposicion', 'show');
});

Route::controller(ClavesController::class)->group(function() {
    Route::post('/claves', 'Validar');
    Route::get('/cpm', 'index');
  
});


//Route::post('/claves', [ClavesController::class, 'Validar']);
Route::post('/reporte-abasto', [ReportesController::class, 'abasto']);

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });