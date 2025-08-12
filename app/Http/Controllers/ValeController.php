<?php

namespace App\Http\Controllers;

use App\Models\Vale;
use Illuminate\Http\Request;

class ValeController extends Controller
{
    /**
     * Mostrar vista de impresión del vale
     */
    public function mostrarVista(Vale $vale)
    {
        // Cargar todas las relaciones necesarias
        $vale->load([
            'mobiliarios.localizacion',
            'movimiento',
            'mobiliario.localizacion'
        ]);
        
        // Obtener los mobiliarios asociados
        $mobiliarios = $vale->mobiliarios;
        
        // Si no hay mobiliarios en la relación many-to-many, intentar cargar el mobiliario individual
        if ($mobiliarios->isEmpty() && $vale->mobiliario_id) {
            $mobiliario = $vale->mobiliario;
        } else {
            $mobiliario = null;
        }

        // Si el vale viene de un Movimiento, incluir esa información
        $movimiento = $vale->movimiento;

        $fecha = now()->format('d/m/Y H:i');

        return view('pdfs.vale-resguardo-print', compact('vale', 'mobiliarios', 'mobiliario', 'movimiento', 'fecha'));
    }
}
