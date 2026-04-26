<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_trabajo', 'total_pagado')) {
                $table->decimal('total_pagado', 10, 2)->default(0)->after('total_orden');
            }

            if (!Schema::hasColumn('ordenes_trabajo', 'pago_completo')) {
                $table->boolean('pago_completo')->default(false)->after('total_pagado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_trabajo', 'pago_completo')) {
                $table->dropColumn('pago_completo');
            }

            if (Schema::hasColumn('ordenes_trabajo', 'total_pagado')) {
                $table->dropColumn('total_pagado');
            }
        });
    }
};
