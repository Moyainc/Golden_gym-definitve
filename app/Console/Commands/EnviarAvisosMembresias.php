<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PagoMembresia;
use App\Mail\MembresiaProximaAVencer;
use App\Mail\MembresiaVencida;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarAvisosMembresias extends Command
{
    protected $signature = 'avisos:membresias';
    protected $description = 'Enviar correos cuando las membresías estén próximas a vencer o vencidas';

    public function handle()
    {
        $hoy = Carbon::now()->startOfDay();

        $pagos = PagoMembresia::whereNotIn('estado', ['anulado'])
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('pagos_membresias')
                    ->whereNotIn('estado', ['anulado'])
                    ->groupBy('cliente_id');
            })
            ->get();

        foreach ($pagos as $pago) {

            $fechaFin = $pago->fecha_fin->copy()->endOfDay();
            $dias = $hoy->diffInDays($fechaFin, false);

          
            if ($dias <= 5 && $dias >= 0 && !$pago->aviso_proximo) {

                Mail::to($pago->cliente->correo)
                    ->send(new MembresiaProximaAVencer($pago));

                $pago->update([
                    'estado_notificado' => 'proxima',
                    'aviso_proximo' => now(),
                ]);
            }

           
            elseif ($dias === -1 && !$pago->aviso_vencido) {

                Mail::to($pago->cliente->correo)
                    ->send(new MembresiaVencida($pago));

                $pago->update([
                    'estado_notificado' => 'vencida',
                    'aviso_vencido' => now(),
                ]);
            }
        }

        $this->info("✔ Avisos procesados correctamente.");
    }
}
