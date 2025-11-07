<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_diarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cliente');
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('metodo_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_diarios');
    }
};
