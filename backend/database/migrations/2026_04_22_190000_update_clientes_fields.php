<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('nombres', 100)->nullable()->after('id_cliente');
            $table->string('apellidos', 100)->nullable()->after('nombres');
            $table->string('carnet_identidad', 30)->nullable()->unique()->after('telefono');
            $table->timestamp('ultima_modificacion')->nullable()->after('fecha_registro');
        });

        DB::table('clientes')
            ->whereNull('nombres')
            ->update(['nombres' => DB::raw('nombre')]);

        DB::table('clientes')
            ->whereNull('ultima_modificacion')
            ->update(['ultima_modificacion' => DB::raw('fecha_registro')]);
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_carnet_identidad_unique');
            $table->dropColumn(['nombres', 'apellidos', 'carnet_identidad', 'ultima_modificacion']);
        });
    }
};
