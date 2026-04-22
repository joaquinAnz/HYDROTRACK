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
        $table->id('id_orden');

        $table->string('codigo_seguimiento')->unique();

        $table->unsignedBigInteger('id_vehiculo');
        $table->unsignedBigInteger('id_tecnico')->nullable();
        $table->unsignedBigInteger('id_usuario_registro');
        $table->unsignedBigInteger('id_tipo_servicio');

        $table->dateTime('fecha_ingreso')->useCurrent();
        $table->dateTime('fecha_salida')->nullable();

        $table->text('descripcion_falla');
        $table->text('diagnostico')->nullable();
        $table->text('observaciones')->nullable();

        $table->decimal('costo_mano_obra', 10, 2)->default(0);
        $table->unsignedBigInteger('id_estado');

        $table->decimal('total_orden', 10, 2)->default(0);

        // Relaciones (foreign keys)
        $table->foreign('id_vehiculo')->references('id_vehiculo')->on('vehiculos');
        $table->foreign('id_tecnico')->references('id_usuario')->on('usuarios');
        $table->foreign('id_usuario_registro')->references('id_usuario')->on('usuarios');
        $table->foreign('id_estado')->references('id_estado')->on('estados_orden');
        $table->foreign('id_tipo_servicio')->references('id_tipo_servicio')->on('tipos_servicio');
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
