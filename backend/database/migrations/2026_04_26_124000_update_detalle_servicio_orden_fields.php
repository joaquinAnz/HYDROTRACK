<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_servicio_orden', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_servicio_orden', 'cantidad')) {
                $table->integer('cantidad')->default(1)->after('id_servicio');
            }

            if (!Schema::hasColumn('detalle_servicio_orden', 'precio_unitario')) {
                $table->decimal('precio_unitario', 10, 2)->default(0)->after('cantidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_servicio_orden', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_servicio_orden', 'precio_unitario')) {
                $table->dropColumn('precio_unitario');
            }

            if (Schema::hasColumn('detalle_servicio_orden', 'cantidad')) {
                $table->dropColumn('cantidad');
            }
        });
    }
};
