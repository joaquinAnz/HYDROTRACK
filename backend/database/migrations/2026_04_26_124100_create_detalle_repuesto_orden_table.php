<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_repuesto_orden', function (Blueprint $table) {
            $table->id('id_detalle_repuesto');
            $table->unsignedBigInteger('id_orden');
            $table->unsignedBigInteger('id_repuesto');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);

            $table->foreign('id_orden')
                ->references('id_orden')
                ->on('ordenes_trabajo')
                ->onDelete('cascade');

            $table->foreign('id_repuesto')
                ->references('id_repuesto')
                ->on('repuestos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_repuesto_orden');
    }
};
