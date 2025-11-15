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

        // Obtener solo el último pago activo por cliente
        $pagos = PagoMembresia::where('estado', 'activa')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('pagos_membresias')
                    ->where('estado', 'activa')
                    ->groupBy('cliente_id');
            })
            ->get();

        foreach ($pagos as $pago) {
            $fechaFin = $pago->fecha_fin->copy()->endOfDay();
            $dias = $hoy->diffInDays($fechaFin, false);

            // -------------------------------
            // Membresía vencida (un día después)
            // -------------------------------
            if ($dias < 0) {
                if ($pago->estado_notificado !== 'vencida' || !$pago->ultimo_aviso_vencida || !Carbon::parse($pago->ultimo_aviso_vencida)->isSameDay($hoy)) {
                    Mail::to($pago->cliente->correo)->send(new MembresiaVencida($pago));

                    $pago->update([
                        'estado_notificado' => 'vencida',
                        'ultimo_aviso_vencida' => Carbon::now(),
                    ]);
                }
            }
            // -------------------------------
            // Membresía próxima a vencer
            // -------------------------------
            elseif ($dias <= 5 && $dias >= 0) {
                if ($pago->estado_notificado !== 'proxima' || !$pago->ultimo_aviso_proxima || !Carbon::parse($pago->ultimo_aviso_proxima)->isSameDay($hoy)) {
                    Mail::to($pago->cliente->correo)->send(new MembresiaProximaAVencer($pago));

                    $pago->update([
                        'estado_notificado' => 'proxima',
                        'ultimo_aviso_proxima' => Carbon::now(),
                    ]);
                }
            }
        }

        $this->info("✔ Avisos procesados correctamente.");
    }
}
