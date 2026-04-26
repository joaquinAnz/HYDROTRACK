<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_actualizaciones', function (Blueprint $table) {
            $table->id('id_actualizacion');
            $table->unsignedBigInteger('id_orden');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('tipo', 40)->default('actualizacion');
            $table->text('descripcion');
            $table->dateTime('fecha')->useCurrent();

            $table->foreign('id_orden')
                ->references('id_orden')
                ->on('ordenes_trabajo')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_actualizaciones');
    }
};
