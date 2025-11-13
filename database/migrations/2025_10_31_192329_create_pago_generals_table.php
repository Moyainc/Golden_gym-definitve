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
            $table->string('tipo_pago'); // nombre corto del tipo de pago
            $table->string('origen_type'); // clase completa del modelo relacionado
            $table->unsignedBigInteger('origen_id'); // id del registro relacionado
            $table->decimal('monto', 15, 0);
            $table->string('metodo_pago');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->dateTime('fecha_pago');
            $table->string('estado')->default('pendiente');
            $table->timestamps();

            // Relación opcional con usuarios
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_generales');
    }
};
