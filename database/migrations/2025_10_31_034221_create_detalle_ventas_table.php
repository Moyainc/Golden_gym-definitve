<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();

            // Relación con venta: si se elimina la venta, sí se eliminan los detalles
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // Relación con producto: si se elimina el producto, el detalle queda intacto
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->decimal('precio_unitario', 10, 1);
            $table->integer('cantidad');
            $table->decimal('subtotal', 10, 1);
            $table->timestamps();

            // Si se elimina el producto, el campo queda nulo (la venta se conserva)
            $table->foreign('producto_id')->references('id')->on('productos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
