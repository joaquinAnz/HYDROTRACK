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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('id_usuario')->autoIncrement();

            $table->string('usuario', 50)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();

            $table->string('password_hash', 255);

            $table->integer('id_rol');
            $table->boolean('estado')->default(1);

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('ultima_modificacion')->nullable();

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
