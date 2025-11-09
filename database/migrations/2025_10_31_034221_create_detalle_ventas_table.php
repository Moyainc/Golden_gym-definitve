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

            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('producto_id')->nullable();
            $table->decimal('precio_unitario', 11, 0);
            $table->integer('cantidad');
            $table->decimal('subtotal', 11, 1);
            $table->timestamps();


            $table->foreign('producto_id')->references('id')->on('productos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
