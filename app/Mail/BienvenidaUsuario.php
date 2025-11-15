<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BienvenidaUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;

    /**
     * Create a new message instance.
     */
    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('¡Bienvenido a Golden Gym!')
            ->markdown('emails.bienvenida')
            ->with([
                'nombre' => $this->usuario->nombre_completo,
                'rol' => $this->usuario->rol,
            ]);
    }
}
