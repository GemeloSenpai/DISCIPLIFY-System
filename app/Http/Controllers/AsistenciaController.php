<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function update(Request $request, Asistencia $asistencia)
{
    // Validamos lo que realmente importa; la hora la normalizamos a mano
    $data = $request->validate([
        'estado'     => ['required','in:presente,tarde,ausente,justificado'],
        'comentario' => ['nullable','string','max:300'],
        'hora_llegada' => ['nullable','string'], // <- sin date_format aquí
    ]);

    $estado = $data['estado'];
    $hora   = $request->input('hora_llegada'); // puede venir HH:MM o HH:MM:SS o ''

    if (in_array($estado, ['ausente','justificado'], true)) {
        // No asistió => limpiamos hora
        $data['hora_llegada'] = null;
    } else {
        // presente/tarde
        if ($hora === null || $hora === '') {
            // si no mandaron hora => ahora mismo
            $data['hora_llegada'] = now()->format('H:i:s');
        } else {
            // Aceptar HH:MM o HH:MM:SS y normalizar a HH:MM:SS
            if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
                $data['hora_llegada'] = $hora . ':00';
            } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
                $data['hora_llegada'] = $hora;
            } else {
                return response()->json([
                    'message' => 'Formato de hora inválido (usa HH:MM o HH:MM:SS).'
                ], 422);
            }
        }
    }

    $asistencia->update([
        'estado'       => $data['estado'],
        'hora_llegada' => $data['hora_llegada'],
        'comentario'   => $data['comentario'] ?? null,
    ]);

    return response()->json([
        'ok' => true,
        'asistencia' => $asistencia->only(['id_asistencias','estado','hora_llegada','comentario']),
    ]);
}


}

