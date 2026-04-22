<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('nombres', 100)->nullable()->after('id_usuario');
            $table->string('apellidos', 100)->nullable()->after('nombres');
            $table->string('telefono', 20)->nullable()->after('apellidos');
            $table->timestamp('ultima_modificacion')->nullable()->after('fecha_creacion');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('estado')->default(1)->after('carnet_identidad');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['nombres', 'apellidos', 'telefono', 'ultima_modificacion']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['estado']);
        });
    }
};
