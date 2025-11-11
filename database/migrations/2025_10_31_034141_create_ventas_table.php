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

            // Relaciones con usuario y cliente (sin borrar la venta si se eliminan)
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();

            // Datos principales de la venta
            $table->decimal('total', 11, 0)->default(0);
            $table->enum('metodo_pago', ['Efectivo', 'Transferencia']);
            $table->enum('estado', ['Completada', 'Pendiente', 'Anulada'])->default('Completada');
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->timestamps();

            // Claves foráneas: si usuario o cliente se eliminan, los campos quedan nulos
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();

            $table->foreign('cliente_id')
                ->references('id')
                ->on('clientes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

