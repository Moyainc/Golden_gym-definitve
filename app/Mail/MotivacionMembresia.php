<?php

namespace App\Mail;

use App\Models\PagoMembresia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MotivacionMembresia extends Mailable
{
    use Queueable, SerializesModels;

    public $pago;
    public $mensajeMotivacion;

    public function __construct(PagoMembresia $pago)
    {
        $this->pago = $pago;

        // Lista de mensajes motivacionales
        $mensajes = [
            "¡No te detengas! Cada entrenamiento te acerca a tu mejor versión.",
            "Hoy es un gran día para superar tus límites.",
            "Cada gota de sudor es un paso hacia tus metas.",
            "¡Mantén la constancia! Tu esfuerzo dará resultados.",
            "Golden Gym te acompaña en cada repetición.",
            "Recuerda: los resultados llegan con disciplina y paciencia.",
            "No cuentes los días, haz que los días cuenten.",
            "El progreso comienza con una decisión, ¡decídete hoy!",
            "Golden Gym: tu esfuerzo es nuestra motivación.",
            "Cada pequeño avance te acerca a tus grandes metas.",
            "No abandones, tu cuerpo y mente te lo agradecerán.",
            "Entrena duro, siente la diferencia, sigue adelante.",
            "Hoy es un buen día para retarte a ti mismo.",
            "La perseverancia rompe cualquier obstáculo.",
            "Golden Gym cree en tu fuerza y dedicación.",
            "El éxito no es casualidad, es trabajo constante.",
            "Disfruta el proceso y celebra cada logro.",
            "Transforma tu energía en resultados positivos.",
            "Tu constancia hoy será tu orgullo mañana.",
            "Cada repetición cuenta, cada esfuerzo suma.",
            "Mantén la motivación y deja que los resultados hablen.",
            "No te compares, compite contigo mismo.",
            "Golden Gym: juntos hacia una mejor versión de ti.",
            "Tu compromiso define tu futuro, ¡sigue adelante!",
        ];

        // Elegir un mensaje aleatorio
        $this->mensajeMotivacion = $mensajes[array_rand($mensajes)];
    }

    public function build()
    {
        return $this->subject('¡Motívate y sigue con tu entrenamiento!')
            ->view('emails.motivacion_membresia')
            ->with([
                'nombre' => $this->pago->cliente->nombre_completo,
                'mensajeMotivacion' => $this->mensajeMotivacion,
                'membresia' => $this->pago->membresia->nombre,
                'fechaInicio' => $this->pago->fecha_inicio?->format('d/m/Y') ?? 'No disponible',
                'fechaFin' => $this->pago->fecha_fin?->format('d/m/Y') ?? 'No disponible',
                'usuarioRegistrador' => $this->pago->usuario,
            ]);
    }
}
