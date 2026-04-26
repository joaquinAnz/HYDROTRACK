<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_metodos_orden', function (Blueprint $table) {
            $table->id('id_pago_metodo');
            $table->unsignedBigInteger('id_pago_orden');
            $table->string('metodo', 20);
            $table->decimal('monto', 10, 2);

            $table->foreign('id_pago_orden')
                ->references('id_pago_orden')
                ->on('pagos_orden')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_metodos_orden');
    }
};
