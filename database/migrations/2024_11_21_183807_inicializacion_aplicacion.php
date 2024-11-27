<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InicializacionAplicacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('almacen', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion',100);
            $table->timestamps();
            $table->softDeletes();
        });*/

        
       Schema::create('catalogo_fecha_cpm', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('catalogo_cpm', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('catalogo_fecha_cpm_id')->unsigned();
            $table->string('gpo',20);
            $table->string('clave',20);
            $table->string('descripcion',250);
            $table->mediumInteger('cpm')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('catalogo_fecha_cpm_id')->references('id')->on('catalogo_fecha_cpm');
        });

        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('archivo',250);
            $table->mediumInteger('total_insumos')->unsigned()->default(0);
            $table->mediumInteger('total_caducados')->unsigned()->default(0);
            $table->mediumInteger('total_claves')->unsigned()->default(0);
            $table->mediumInteger('total_claves_cpm')->unsigned()->default(0);
            $table->mediumInteger('total_insumos_importados')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventario_detalles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('almacen_id')->unsigned();
            $table->bigInteger('inventario_id')->unsigned();
            $table->bigInteger('catalogo_cpm_id')->unsigned();
            $table->string('lote',100);
            $table->date('caducidad');
            $table->string('financiamiento', 100);
            $table->mediumInteger('existencia');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventario_id')->references('id')->on('inventario');
            $table->foreign('almacen_id')->references('id')->on('almacen');
            $table->foreign('catalogo_cpm_id')->references('id')->on('catalogo_cpm');
        });


        

        Schema::create('disposicion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('archivo', 250);
            $table->mediumInteger('total_insumos')->unsigned()->default(0);
            $table->mediumInteger('total_caducados')->unsigned()->default(0);
            $table->mediumInteger('total_claves')->unsigned()->default(0);
            $table->mediumInteger('total_claves_cpm')->unsigned()->default(0);
            $table->mediumInteger('total_insumos_importados')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('disposicion_detalles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('disposicion_id')->unsigned();
            $table->bigInteger('catalogo_cpm_id')->unsigned();
            $table->string('lote',100);
            $table->date('caducidad');
            $table->string('financiamiento', 100);
            $table->mediumInteger('existencia');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('disposicion_id')->references('id')->on('disposicion');
            $table->foreign('catalogo_cpm_id')->references('id')->on('catalogo_cpm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disposicion_detalles');
        Schema::dropIfExists('disposicion');
        Schema::dropIfExists('inventario_detalles');
        Schema::dropIfExists('inventario');
        Schema::dropIfExists('catalogo_cpm');
        Schema::dropIfExists('catalogo_fecha_cpm');
        /*Schema::dropIfExists('almacen');*/
    }
}
