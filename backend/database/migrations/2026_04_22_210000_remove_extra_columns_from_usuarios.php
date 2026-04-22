<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Eliminar columna heredada duplicada.
            if (Schema::hasColumn('usuarios', 'nombre')) {
                $table->dropColumn('nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'nombre')) {
                $table->string('nombre', 100)->nullable()->after('id_usuario');
            }
        });
    }
};
