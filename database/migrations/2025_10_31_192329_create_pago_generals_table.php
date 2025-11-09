<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_generales', function (Blueprint $table) {
            $table->id();

            // 🔹 Fuente del pago: venta, membresía, pago individual, etc.
            $table->string('tipo_pago');
            // Ejemplo: 'venta_producto', 'membresia', 'pago_individual', etc.

            // 🔹 ID del registro en la tabla de origen (por ejemplo, id de la venta)
            $table->morphs('origen');

            // 🔹 Información contable y general
            $table->decimal('monto', 11, 0); // total pagado
            $table->string('metodo_pago');   // Efectivo, Transferencia, etc.
            $table->unsignedBigInteger('usuario_id')->nullable(); // Usuario que registró el pago
            $table->timestamp('fecha_pago')->useCurrent(); // Fecha en que se registró el pago

            $table->timestamps();

            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_generales');
    }
};
