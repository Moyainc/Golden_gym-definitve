<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Mail\CorreoMasivo;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\PagoMembresia;
use Carbon\Carbon;

class MailMasivoController extends Controller
{
    public function create()
    {
        return view('admin.mail_masivo');
    }

    private function calcularEstado(PagoMembresia $pago)
    {
        $hoy = Carbon::now();
        $fin = Carbon::parse($pago->fecha_fin);

        if ($fin->isPast()) {
            return 'vencida';
        }

        $diasRestantes = $hoy->diffInDays($fin, false);

        if ($diasRestantes <= 5) {
            return 'proxima';
        }

        return 'activa';
    }

    private function obtenerClientesPorEstado($estadoDeseado)
    {
        $pagos = PagoMembresia::with('cliente')
            ->whereNotNull('fecha_fin')
            ->get();

        $clientesFiltrados = collect();

        foreach ($pagos as $pago) {

            if (!$pago->cliente || !$pago->cliente->correo) continue;

            $estado = $this->calcularEstado($pago);

            if ($estado === $estadoDeseado) {
                $clientesFiltrados->push($pago->cliente);
            }
        }

        return $clientesFiltrados->unique('id');
    }

    /**
     * ENVIAR CORREO MASIVO
     */
    public function send(Request $request)
    {
        $request->validate([
            'asunto'   => 'required|string|max:255',
            'mensaje'  => 'required|string',
            'segmento' => 'required|string',
            'imagenes.*' => 'nullable|image|max:2048' // imágenes opcionales
        ]);

        $asunto = $request->asunto;
        $mensaje = $request->mensaje;
        $segmento = $request->segmento;

        // Procesar imágenes opcionales
        $imagenesAdjuntas = [];
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $img) {
                $imagenesAdjuntas[] = $img->store('correos_adjuntos', 'public');
            }
        }

        // Selección de destinatarios
        switch ($segmento) {

            case 'clientes':
                $destinatarios = Cliente::whereNotNull('correo')->get();
                break;

            case 'usuarios':
                $destinatarios = Usuario::whereNotNull('correo')->get();
                break;

            case 'membresias_activas':
                $destinatarios = $this->obtenerClientesPorEstado('activa');
                break;

            case 'membresias_proximas':
                $destinatarios = $this->obtenerClientesPorEstado('proxima');
                break;

            case 'membresias_vencidas':
                $destinatarios = $this->obtenerClientesPorEstado('vencida');
                break;

            case 'todos':
            default:
                $destinatarios = Cliente::whereNotNull('correo')->get()
                    ->concat(Usuario::whereNotNull('correo')->get());
                break;
        }

        if ($destinatarios->isEmpty()) {
            return back()->with('error', 'No se encontraron destinatarios.');
        }

        foreach ($destinatarios as $persona) {

            $email = $persona->correo ?? $persona->email;

            if (!$email) continue;

            Mail::to($email)->send(
                new CorreoMasivo($asunto, $mensaje, $imagenesAdjuntas)
            );
        }

        return back()->with('success', "Correo enviado a {$destinatarios->count()} destinatarios.");
    }

    /**
     * PREVISUALIZAR CANTIDAD
     */
    public function contarSegmento(Request $request)
    {
        $segmento = $request->segmento;

        switch ($segmento) {
            case 'todos':
                $cantidad = Usuario::count();
                break;

            case 'clientes':
                $cantidad = Cliente::count();
                break;

            case 'usuarios':
                $cantidad = Usuario::count();
                break;

            case 'membresias_activas':
                $cantidad = PagoMembresia::where('estado', 'activa')->count();
                break;

            case 'membresias_proximas':
                $cantidad = PagoMembresia::where('estado', 'proxima')->count();
                break;

            case 'membresias_vencidas':
                $cantidad = PagoMembresia::where('estado', 'vencida')->count();
                break;

            default:
                $cantidad = 0;
        }

        return response()->json(['cantidad' => $cantidad]);
    }
}
