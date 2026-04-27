<?php

namespace App\Http\Controllers;

use App\Models\PersonalReportante;
use App\Models\ReportePizarron;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalReportesController extends Controller
{
    // ── Login ──────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::guard('personal')->check()) {
            return redirect()->route('portal.reportes.form');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'numero_empleado' => 'required|string',
            'password'        => 'required|string',
        ], [
            'numero_empleado.required' => 'Ingresa tu número de empleado.',
            'password.required'        => 'Ingresa tu contraseña.',
        ]);

        $personal = PersonalReportante::where('numero_empleado', $request->numero_empleado)->first();

        if (! $personal || ! Hash::check($request->password, $personal->password)) {
            return back()->withErrors(['numero_empleado' => 'Número de empleado o contraseña incorrectos.'])->withInput();
        }

        if ($personal->estado === 'pendiente') {
            return back()->withErrors(['numero_empleado' => 'Tu registro está pendiente de aprobación. Te avisaremos cuando esté activo.'])->withInput();
        }

        if ($personal->estado === 'rechazado') {
            return back()->withErrors(['numero_empleado' => 'Tu solicitud de registro fue rechazada. Contacta al departamento de Ingeniería Biomédica.'])->withInput();
        }

        Auth::guard('personal')->login($personal, $request->boolean('remember'));

        return redirect()->route('portal.reportes.form');
    }

    public function logout(Request $request)
    {
        Auth::guard('personal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    // ── Registro ───────────────────────────────────────────────────────────

    public function showRegistro()
    {
        if (Auth::guard('personal')->check()) {
            return redirect()->route('portal.reportes.form');
        }
        return view('portal.registro');
    }

    public function registro(Request $request)
    {
        $esJefe = $request->boolean('es_jefe_servicio');

        $request->validate([
            'nombre'              => 'required|string|max:100',
            'numero_empleado'     => 'required|string|max:20|unique:personal_reportante,numero_empleado',
            'servicio'            => 'required|string|max:100',
            'password'            => 'required|string|min:6|confirmed',
            'firma'               => 'required|string',
            'area_jefe_servicio'  => $esJefe ? 'required|string|max:100' : 'nullable|string|max:100',
            'horario_inicio'      => 'required|date_format:H:i',
            'horario_fin'         => 'required|date_format:H:i|after:horario_inicio',
        ], [
            'nombre.required'                 => 'Ingresa tu nombre completo.',
            'numero_empleado.required'        => 'Ingresa tu número de empleado.',
            'numero_empleado.unique'          => 'Este número de empleado ya está registrado.',
            'servicio.required'               => 'Ingresa tu servicio o área.',
            'password.required'               => 'Elige una contraseña.',
            'password.min'                    => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'              => 'Las contraseñas no coinciden.',
            'firma.required'                  => 'Dibuja o sube tu firma para completar el registro.',
            'area_jefe_servicio.required'     => 'Selecciona el área de la que eres Jefe de Servicio.',
            'horario_inicio.required'         => 'Ingresa la hora de entrada de tu turno.',
            'horario_inicio.date_format'      => 'Formato de hora inválido.',
            'horario_fin.required'            => 'Ingresa la hora de salida de tu turno.',
            'horario_fin.date_format'         => 'Formato de hora inválido.',
            'horario_fin.after'               => 'La hora de salida debe ser posterior a la de entrada.',
        ]);

        PersonalReportante::create([
            'nombre'             => $request->nombre,
            'numero_empleado'    => $request->numero_empleado,
            'servicio'           => $request->servicio,
            'password'           => Hash::make($request->password),
            'estado'             => 'pendiente',
            'firma'              => $request->firma,
            'es_jefe_servicio'   => $esJefe,
            'area_jefe_servicio' => $esJefe ? $request->area_jefe_servicio : null,
            'horario_inicio'     => $request->horario_inicio,
            'horario_fin'        => $request->horario_fin,
        ]);

        return view('portal.registro-enviado');
    }

    // ── Formulario de reporte ──────────────────────────────────────────────

    public function showForm()
    {
        return view('portal.form');
    }

    public function showFirmas()
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($personal->es_jefe_servicio, 403);

        $base = \App\Models\FirmaSolicitud::with(['reporte', 'reporte.bitacora'])
            ->where('personal_reportante_id', $personal->id)
            ->latest();

        $pendientes = (clone $base)->where('estado', 'pendiente')->get();
        $firmadas   = (clone $base)->where('estado', 'firmado')->get();

        return view('portal.firmas', compact('pendientes', 'firmadas', 'personal'));
    }

    public function firmar(Request $request, \App\Models\FirmaSolicitud $solicitud)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($solicitud->personal_reportante_id === $personal->id, 403);
        abort_unless($solicitud->estado === 'pendiente', 400);

        // Para la vista de firma por jefe: usa la firma registrada directamente
        $firmaData = $request->input('firma_data') ?: $personal->firma;

        if (! $firmaData) {
            return back()->withErrors(['firma' => 'No tienes firma registrada.']);
        }

        $solicitud->update([
            'estado'     => 'firmado',
            'firmado_at' => now(),
            'firma_data' => $firmaData,
        ]);

        // Marcar el reporte como concretado ahora que el jefe firmó
        if ($solicitud->reporte_pizarron_id) {
            \App\Models\ReportePizarron::where('id', $solicitud->reporte_pizarron_id)
                ->update(['concretado' => true, 'concretado_at' => now()]);
        }

        return back()->with('firmado_id', $solicitud->id);
    }

    public function showFirmarSolicitud(\App\Models\FirmaSolicitud $solicitud)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($solicitud->personal_reportante_id === $personal->id, 403);

        return view('portal.firmar-solicitud', compact('solicitud', 'personal'));
    }


    public function portalBitacoraPdf(\App\Models\BitacoraReporte $bitacora)
    {
        $personal = Auth::guard('personal')->user();

        // Autorizar: es el reportante original o el jefe que firmó
        $esReportante = $bitacora->reporte?->personal_reportante_id === $personal->id;
        $esJefe       = \App\Models\FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('personal_reportante_id', $personal->id)
            ->exists();

        abort_unless($esReportante || $esJefe, 403);

        $meses = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre',
        ];
        $mesEspanol     = $meses[\Carbon\Carbon::parse($bitacora->fecha_reporte)->month];
        $textoResultado = match($bitacora->resultado) {
            'parcial'          => 'parcial',
            'no_satisfactoria' => 'no satisfactoria',
            default            => 'satisfactoria',
        };
        $labelResultado = match($bitacora->resultado) {
            'parcial'          => 'Parcial',
            'no_satisfactoria' => 'No satisfactoria',
            default            => 'Satisfactoria',
        };
        $firmaSolicitud = \App\Models\FirmaSolicitud::where('reporte_pizarron_id', $bitacora->reporte_pizarron_id)
            ->where('estado', 'firmado')->latest()->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bitacora', compact(
            'bitacora', 'mesEspanol', 'textoResultado', 'labelResultado', 'firmaSolicitud'
        ))->setPaper('letter', 'portrait');

        return $pdf->stream('Bitacora_' . $bitacora->id . '.pdf');
    }

    public function enviar(Request $request, GeminiService $gemini)
    {
        $personal = Auth::guard('personal')->user();

        // Verificar que el envío esté dentro del horario laboral registrado
        if ($personal->horario_inicio && $personal->horario_fin) {
            $ahora  = now()->format('H:i');
            $inicio = substr($personal->horario_inicio, 0, 5);
            $fin    = substr($personal->horario_fin, 0, 5);

            $dentroDeHorario = $inicio <= $fin
                ? ($ahora >= $inicio && $ahora <= $fin)
                : ($ahora >= $inicio || $ahora <= $fin); // turno nocturno que cruza medianoche

            if (! $dentroDeHorario) {
                return back()->withErrors([
                    'descripcion' => "Solo puedes enviar reportes dentro de tu horario laboral ({$inicio} – {$fin}).",
                ])->withInput();
            }
        }

        $request->validate([
            'descripcion' => 'required|string|min:10|max:1000',
        ], [
            'descripcion.required' => 'Describe la falla antes de enviar.',
            'descripcion.min'      => 'Por favor da un poco más de detalle (mínimo 10 caracteres).',
        ]);

        // La IA extrae equipo, ubicación, descripción limpia y prioridad sugerida
        $datos = $gemini->extraerDatosReporte($request->descripcion, $personal->servicio);

        ReportePizarron::create([
            'titulo'                 => 'Reporte — ' . $personal->servicio,
            'equipo'                 => $datos['equipo'],
            'ubicacion'              => $datos['ubicacion'],
            'descripcion'            => $request->descripcion,
            'descripcion_original'   => $request->descripcion,
            'prioridad'              => $datos['prioridad'],
            'estado'                 => 'pendiente',
            'minimizado'             => false,
            'personal_reportante_id' => $personal->id,
            'reportante_nombre'      => $personal->nombre,
            'reportante_servicio'    => $personal->servicio,
        ]);

        return back()->with('enviado', true);
    }
}
