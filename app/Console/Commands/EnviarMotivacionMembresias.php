<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PagoMembresia;
use App\Mail\MotivacionMembresia;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarMotivacionMembresias extends Command
{
    protected $signature = 'avisos:motivacion';
    protected $description = 'Enviar correos de motivación a clientes con membresía mensual en el día 10 de su membresía';

    public function handle()
    {
        $hoy = Carbon::now()->startOfDay();

        // Obtener pagos activos de membresía mensual con cliente que tenga correo
        $pagos = PagoMembresia::where('estado', 'activa')
            ->whereHas('membresia', function ($query) {
                $query->where('nombre', 'Mensual');
            })
            ->whereHas('cliente', function ($query) {
                $query->whereNotNull('correo');
            })
            ->get();

        $this->info("Se encontraron {$pagos->count()} pagos activos mensuales.");

        foreach ($pagos as $pago) {
            // Calcular el día 10 de la membresía
            $dia10 = $pago->fecha_inicio->copy()->addDays(10)->startOfDay();

            // Solo enviar si hoy es el día 10
            if ($hoy->equalTo($dia10)) {
                Mail::to($pago->cliente->correo)
                    ->send(new \App\Mail\MotivacionMembresia($pago));

                $this->info("✔ Correo de motivación enviado a {$pago->cliente->nombre_completo}");
            }
        }

        $this->info("✔ Proceso finalizado.");
    }
}
