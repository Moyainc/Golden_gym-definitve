<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('foto_comprobante')->nullable();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('id_cliente')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('id_pago_membresia')->nullable()->constrained('pagos_membresias')->nullOnDelete();
            $table->foreignId('id_pagos_diarios')->nullable()->constrained('pagos_diarios')->nullOnDelete();
            $table->foreignId('id_ventas')->nullable()->constrained('ventas')->nullOnDelete();

            $table->string('estado')->default('pendiente'); // 'pendiente' o 'completado'
            $table->timestamp('fecha_subida')->nullable();  // fecha en que se subió el pantallazo

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
