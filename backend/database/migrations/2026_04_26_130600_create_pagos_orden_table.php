<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_orden', function (Blueprint $table) {
            $table->id('id_pago_orden');
            $table->unsignedBigInteger('id_orden');
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_pago')->useCurrent();

            $table->foreign('id_orden')
                ->references('id_orden')
                ->on('ordenes_trabajo')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_orden');
    }
};
