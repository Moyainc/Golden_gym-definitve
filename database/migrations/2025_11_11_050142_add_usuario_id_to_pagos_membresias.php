<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_membresias', function (Blueprint $table) {
            $table->foreignId('usuario_id')
                ->nullable()
                ->after('metodo_pago')
                ->constrained('usuarios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_membresias', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn('usuario_id');
        });
    }
};
