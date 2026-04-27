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
        Schema::create('detalle_repuesto_orden', function (Blueprint $table) {
            $table->integer('id_detalle_repuesto')->autoIncrement();
            $table->integer('id_orden');
            $table->integer('id_repuesto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);

            $table->foreign('id_orden')
                ->references('id_orden')->on('ordenes_trabajo');

            $table->foreign('id_repuesto')
                ->references('id_repuesto')->on('repuestos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_repuesto_orden');
    }
};
