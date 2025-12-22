<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_membresias', function (Blueprint $table) {
            $table->timestamp('aviso_proximo')->nullable()->after('usuario_id');
            $table->timestamp('aviso_vencido')->nullable()->after('aviso_proximo');
            $table->string('estado_notificado')->nullable()->after('aviso_vencido');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_membresias', function (Blueprint $table) {
            $table->dropColumn(['aviso_proximo', 'aviso_vencido', 'estado_notificado']);
        });
    }
};
