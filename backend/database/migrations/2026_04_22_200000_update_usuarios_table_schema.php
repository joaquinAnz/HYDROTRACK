<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si los campos ya existen antes de agregarlos
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'nombres')) {
                $table->string('nombres', 100)->nullable()->after('id_usuario');
            }
            if (!Schema::hasColumn('usuarios', 'apellidos')) {
                $table->string('apellidos', 100)->nullable()->after('nombres');
            }
            if (!Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono', 20)->nullable()->after('apellidos');
            }
            if (!Schema::hasColumn('usuarios', 'ultima_modificacion')) {
                $table->timestamp('ultima_modificacion')->nullable()->after('fecha_creacion');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'estado')) {
                $table->boolean('estado')->default(1)->after('carnet_identidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'nombres')) {
                $table->dropColumn('nombres');
            }
            if (Schema::hasColumn('usuarios', 'apellidos')) {
                $table->dropColumn('apellidos');
            }
            if (Schema::hasColumn('usuarios', 'telefono')) {
                $table->dropColumn('telefono');
            }
            if (Schema::hasColumn('usuarios', 'ultima_modificacion')) {
                $table->dropColumn('ultima_modificacion');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
