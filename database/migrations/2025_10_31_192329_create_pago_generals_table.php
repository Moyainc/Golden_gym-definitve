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

            // Fuente del pago: venta, membresía, pago individual, etc.
            $table->string('tipo_pago'); // 'venta_producto', 'membresia', 'pago_individual', etc.
            $table->unsignedBigInteger('origen_id'); // ID de la tabla específica según el tipo

            // Información contable y de filtros
            $table->decimal('monto', 10, 1);
            $table->string('metodo_pago');
            $table->unsignedBigInteger('usuario_id')->nullable(); // usuario que registró el pago
            $table->timestamp('fecha_pago')->useCurrent();

            $table->timestamps();

            // Llaves foráneas
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_generales');
    }
};
