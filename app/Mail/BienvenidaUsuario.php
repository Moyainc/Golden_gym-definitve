<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BienvenidaUsuario extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $usuario;

    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

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
