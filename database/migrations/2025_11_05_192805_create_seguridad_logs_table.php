<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seguridad_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('correo_intentado')->nullable(); // Si el login falla
            $table->boolean('exitoso')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('dispositivo', 255)->nullable(); // user_agent simplificado
            $table->string('ubicacion', 255)->nullable(); // ciudad / país
            $table->timestamp('fecha_intento')->useCurrent();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguridad_logs');
    }
};
