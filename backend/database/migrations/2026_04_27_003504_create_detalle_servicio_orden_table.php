<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_servicio_orden', function (Blueprint $table) {
            $table->integer('id_detalle_servicio')->autoIncrement();

            $table->integer('id_orden');
            $table->integer('id_servicio');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10,2);
            $table->decimal('precio', 10,2);

            $table->foreign('id_orden')->references('id_orden')->on('ordenes_trabajo');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios_adicionales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_servicio_orden');
    }
};
