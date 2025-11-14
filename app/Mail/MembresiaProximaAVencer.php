<?php

namespace App\Mail;

use App\Models\PagoMembresia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MembresiaProximaAVencer extends Mailable
{
    use Queueable, SerializesModels;

    public $pago;

    public function __construct(PagoMembresia $pago)
    {
        $this->pago = $pago;
    }

    public function build()
    {
        return $this->subject('Tu membresía está próxima a vencer')
            ->view('emails.membresia_proxima_vencer')
            ->with([
                'nombre' => $this->pago->cliente->nombre_completo,
                'membresia' => $this->pago->membresia->nombre,
                'fechaInicio' => $this->pago->fecha_inicio->format('d/m/Y'),
                'fechaFin' => $this->pago->fecha_fin->format('d/m/Y'),
                'valor' => $this->pago->monto,
                'metodoPago' => $this->pago->metodo_pago,
                'usuarioRegistrador' => $this->pago->usuario,
            ]);
    }
}
