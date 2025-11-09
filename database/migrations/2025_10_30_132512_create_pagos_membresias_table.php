<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_membresias', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('cascade');

            // Si existe la tabla 'membresias'
            $table->foreignId('membresia_id')
                ->nullable()
                ->constrained('membresias')
                ->onDelete('set null');

            // Datos del pago
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('monto', 11, 0);
            $table->string('estado')->default('activo');
            $table->string('metodo_pago')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_membresias');
    }
};
