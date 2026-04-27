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
        Schema::create('pagos_orden', function (Blueprint $table) {
            $table->integer('id_pago_orden')->autoIncrement();

            $table->integer('id_orden');
            $table->decimal('monto_total', 10,2);

            $table->string('observacion')->nullable();

            $table->timestamp('fecha_pago')->useCurrent();

            $table->foreign('id_orden')->references('id_orden')->on('ordenes_trabajo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_orden');
    }
};
