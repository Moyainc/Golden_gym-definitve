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
    $email = $this->subject($this->asunto)
                  ->view('emails.correo_masivo')
                  ->with([
                      'mensaje' => $this->mensaje,
                      'imagenes' => [] // ya no las mostramos en el HTML
                  ]);

    // Adjuntar cada archivo
    foreach ($this->imagenes as $imgPath) {

        $absolute = storage_path("app/public/{$imgPath}");

        if (file_exists($absolute)) {
            $email->attach($absolute);
        }
    }

    return $email->with([
        'mensaje'     => $this->mensaje,
        'hayAdjuntos' => count($this->imagenes) > 0
    ]);
}
}
