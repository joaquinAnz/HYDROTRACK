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
        Schema::create('clientes', function (Blueprint $table) {
            $table->integer('id_cliente')->autoIncrement();

            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('carnet_identidad', 30)->unique();

            $table->string('telefono', 20)->nullable();
            $table->boolean('estado')->default(1);

            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('ultima_modificacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
