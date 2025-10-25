<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre_completo', 150);
                $table->string('correo', 100)->unique();
                $table->string('cedula', 20)->unique();
                $table->string('telefono', 10);
                $table->string('foto', 255)->nullable();
                $table->enum('rol', ['Administrador', 'Contador', 'Recepcionista', 'Usuario']);
                $table->string('password', 255);
                $table->dateTime('ultimo_acceso')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
