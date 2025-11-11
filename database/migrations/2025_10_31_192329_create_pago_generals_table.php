<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pagos_generales', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_pago'); // nombre corto del tipo de pago, opcional si usas morphMap
            $table->string('origen_type'); // clase completa del modelo relacionado
            $table->unsignedBigInteger('origen_id'); // id del registro relacionado
            $table->decimal('monto', 15, 0);
            $table->string('metodo_pago');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->dateTime('fecha_pago');
            $table->enum('estado', ['Completada', 'Pendiente', 'Anulada'])->default('Pendiente');

            $table->timestamps();

            // Relación opcional con usuarios
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete(); // Si borran el usuario, se pone null en usuario_id
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_generales');
    }
};
