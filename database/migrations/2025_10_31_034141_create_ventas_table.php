<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // Relaciones con usuario y cliente, sin borrar la venta si se eliminan
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->decimal('total', 11, 0);
            $table->string('metodo_pago');
            $table->string('estado')->default('Completada');
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->timestamps();

            // Si usuario o cliente se eliminan, los campos quedan nulos pero la venta sigue
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
