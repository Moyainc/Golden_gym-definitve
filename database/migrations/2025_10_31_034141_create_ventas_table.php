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
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('cliente_id')
                ->nullable()
                ->nullOnDelete();
                // ->constrained('cliente_id')

            $table->decimal('total', 10, 1);
            $table->string('metodo_pago');
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
