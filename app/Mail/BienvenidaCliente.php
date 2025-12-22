<?php

namespace App\Mail;

use App\Models\Cliente;
use App\Models\PagoMembresia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BienvenidaCliente extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $cliente;
    public $pagoMembresia;

    public function __construct(Cliente $cliente, PagoMembresia $pagoMembresia)
    {
        $this->cliente = $cliente;
        $this->pagoMembresia = $pagoMembresia;
    }

    public function build()
    {
        return $this
            ->subject('¡Bienvenido a Golden Gym!')
            ->markdown('emails.bienvenida_cliente')
            ->with([
                'nombre' => $this->cliente->nombre_completo,
                'membresia' => $this->pagoMembresia->membresia->nombre,
                'fechaInicio' => $this->pagoMembresia->fecha_inicio->format('d/m/Y'),
                'fechaFin' => $this->pagoMembresia->fecha_fin->format('d/m/Y'),
                'metodoPago' => $this->pagoMembresia->metodo_pago,
                'valor' => $this->pagoMembresia->monto,
                'usuarioRegistrador' => $this->pagoMembresia->usuario,
            ]);
    }
}
