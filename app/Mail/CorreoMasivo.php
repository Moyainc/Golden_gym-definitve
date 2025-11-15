<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoMasivo extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $mensaje;
    public $imagenes; // ← AGREGADO

    /**
     * @param string $asunto
     * @param string $mensaje   HTML permitido
     * @param array|null $imagenes  Rutas de imágenes o null
     */
    public function __construct(string $asunto, string $mensaje, ?array $imagenes = null)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->imagenes = $imagenes ?? [];
    }

    public function build()
    {
        return $this->subject($this->asunto)
            ->view('emails.correo_masivo')   // ← LA VISTA CORRECTA
            ->with([
                'mensaje'  => $this->mensaje,
                'imagenes' => $this->imagenes,
            ]);
    }
}
