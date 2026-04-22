<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Dropear índices si existen
            try {
                $table->dropUnique(['correo']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Eliminar columnas no necesarias
            $columns = Schema::getColumnListing('usuarios');
            
            if (in_array('nombre', $columns)) {
                $table->dropColumn('nombre');
            }
            if (in_array('correo', $columns)) {
                $table->dropColumn('correo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'nombre')) {
                $table->string('nombre', 100)->nullable()->after('id_usuario');
            }
            if (!Schema::hasColumn('usuarios', 'correo')) {
                $table->string('correo', 100)->nullable()->unique()->after('password_hash');
            }
        });
    }
};
