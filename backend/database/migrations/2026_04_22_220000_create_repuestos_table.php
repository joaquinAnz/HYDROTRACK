<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repuestos', function (Blueprint $table) {
            $table->id('id_repuesto');
            $table->string('producto', 120);
            $table->string('codigo', 30)->unique();
            $table->integer('stock')->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('estado')->default(1);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('ultima_modificacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repuestos');
    }
};
