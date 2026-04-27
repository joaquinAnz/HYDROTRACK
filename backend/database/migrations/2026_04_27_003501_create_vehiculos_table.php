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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->integer('id_vehiculo')->autoIncrement();

            $table->integer('id_cliente');

            $table->string('placa', 20)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('estado')->default(1);

            $table->string('marca', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->integer('anio')->nullable();

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
