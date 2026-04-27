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
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
    $table->integer('id_orden')->autoIncrement();

    $table->string('codigo_seguimiento', 30)->unique();

    $table->integer('id_vehiculo');
    $table->integer('id_tipo_servicio');

    $table->integer('id_tecnico')->nullable();
    $table->integer('id_usuario_registro');

    $table->timestamp('fecha_ingreso')->useCurrent();
    $table->timestamp('fecha_salida')->nullable();

    $table->text('descripcion_falla');
    $table->text('diagnostico')->nullable();
    $table->text('observaciones')->nullable();

    $table->decimal('costo_mano_obra', 10, 2)->default(0);

    $table->integer('id_estado');

    $table->decimal('total_orden', 10, 2)->default(0);
    $table->decimal('total_pagado', 10, 2)->default(0);

    $table->boolean('pago_completo')->default(0);

    // 🔗 FOREIGN KEYS
    $table->foreign('id_vehiculo')
        ->references('id_vehiculo')->on('vehiculos');

    $table->foreign('id_tipo_servicio')
        ->references('id_tipo_servicio')->on('tipos_servicio');

    $table->foreign('id_tecnico')
        ->references('id_usuario')->on('usuarios');

    $table->foreign('id_usuario_registro')
        ->references('id_usuario')->on('usuarios');

    $table->foreign('id_estado')
        ->references('id_estado')->on('estados_orden');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_trabajo');
    }
};
