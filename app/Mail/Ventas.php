<?php

namespace App\Mail;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Ventas extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;

    /**
     * Crear una nueva instancia del Mailable.
     *
     * @param \App\Models\Venta $venta
     */
    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    /**
     * Construir el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Detalle de tu compra en Golden Gym')
                    ->view('emails.ventas')
                    ->with([
                        'cliente' => $this->venta->cliente,
                        'usuario' => $this->venta->usuario, // Quién vendió
                        'venta' => $this->venta,
                        'productos' => $this->venta->detalles, // Detalles de la venta
                        'total' => $this->venta->total,
                        'metodoPago' => $this->venta->metodo_pago,
                    ]);
    }
}
