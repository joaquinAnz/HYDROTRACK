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
        Schema::create('orden_actualizaciones', function (Blueprint $table) {
            $table->integer('id_actualizacion')->autoIncrement();

            $table->integer('id_orden');
            $table->integer('id_usuario');

            $table->string('tipo', 50);
            $table->text('descripcion');

            $table->timestamp('fecha')->useCurrent();

            $table->foreign('id_orden')->references('id_orden')->on('ordenes_trabajo');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_actualizaciones');
    }
};
