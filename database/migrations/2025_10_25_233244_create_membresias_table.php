<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
<<<<<<< HEAD
            $table->integer('duracion'); // Ej: 30
            $table->enum('tipo_duracion', ['dias', 'meses']); // solo permite 'dias' o 'meses'
            $table->decimal('precio', 10, 2);
=======
            $table->decimal('precio', 10, 1);
            $table->integer('duracion_meses');
            $table->enum('activo', ['Activa', 'Inactiva'])->default('Activa');
>>>>>>> 3672f60aa864332e7ea9dbdf70cee6f21b94b9ac
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
