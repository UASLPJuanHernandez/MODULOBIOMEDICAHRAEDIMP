<?php

namespace App\Http\Controllers;

use App\Models\PersonalReportante;
use App\Models\Registro;
use App\Models\ReportePizarron;
use App\Services\AuditService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        Auth::guard('personal')->login($personal, $request->boolean('remember'));

        AuditService::log('acceso', "Inicio de sesión en /reportes — {$personal->nombre}", [
            'actor_tipo'   => 'personal',
            'actor_id'     => $personal->id,
            'actor_nombre' => $personal->nombre,
            'origen'       => '/reportes',
        ]);

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
        abort_unless(Auth::check(), 403);
        return view('portal.registro');
    }

    public function registro(Request $request)
    {
        abort_unless(Auth::check(), 403);
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
            'estado'             => 'aprobado',
            'firma'              => $request->firma,
            'es_jefe_servicio'   => $esJefe,
            'area_jefe_servicio' => $esJefe ? $request->area_jefe_servicio : null,
            'horario_inicio'     => $request->horario_inicio,
            'horario_fin'        => $request->horario_fin,
        ]);

        return redirect('/admin/personal-reportantes')
            ->with('success', 'Usuario registrado correctamente.');
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

        $registros = Registro::where('jefe_id', $personal->id)
            ->whereIn('estado', ['en_firma', 'culminado'])
            ->with(['formato:id,nombre', 'usuario:id,name'])
            ->latest()
            ->get();

        $porTipo = [
            'mantenimiento' => $registros->where('tipo_documento', 'mantenimiento'),
            'reporte'       => $registros->where('tipo_documento', 'reporte'),
            'vale'          => $registros->where('tipo_documento', 'vale'),
            'documento'     => $registros->where('tipo_documento', 'documento'),
        ];

        $solicitudesFirma = \App\Models\FirmaSolicitud::where('personal_reportante_id', $personal->id)
            ->with([
                'reporte:id,titulo,equipo,ubicacion,descripcion,created_at',
                'reporte.bitacora:id,reporte_pizarron_id',
            ])
            ->latest()
            ->get();

        $valesAsignados = \App\Models\ValeInventario::where('jefe_id', $personal->id)
            ->whereIn('estado', ['en_firma', 'culminado'])
            ->latest()
            ->get();

        return view('portal.firmas', compact('personal', 'porTipo', 'solicitudesFirma', 'valesAsignados'));
    }

    public function confirmarVale(\App\Models\ValeInventario $vale)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($vale->jefe_id === $personal->id, 403);
        abort_unless($vale->estado === 'en_firma', 400);

        $vale->update([
            'estado'        => 'culminado',
            'firmado_at'    => now(),
            'concretado_at' => now(),
        ]);

        return back()->with('vale_confirmado', $vale->id);
    }

    public function firmar(Request $request, \App\Models\FirmaSolicitud $solicitud)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($solicitud->personal_reportante_id === $personal->id, 403);
        abort_unless($solicitud->estado === 'pendiente', 400);

        $request->validate([
            'firma_posicion' => 'required|string',
        ], [
            'firma_posicion.required' => 'Coloca tu firma en el documento antes de confirmar.',
        ]);

        $firmaData = $request->input('firma_data') ?: $personal->firma;

        if (! $firmaData) {
            return back()->withErrors(['firma' => 'No tienes firma registrada.']);
        }

        // Guardar posición + imagen juntos para que BitacoraOverlayService
        // pueda colocar la firma exactamente donde el jefe la posicionó
        $savedData = json_encode([
            'posicion' => json_decode($request->firma_posicion, true),
            'imagen'   => $firmaData,
        ]);

        $solicitud->update([
            'estado'     => 'firmado',
            'firmado_at' => now(),
            'firma_data' => $savedData,
        ]);

        AuditService::log('firma', "Reporte #{$solicitud->reporte_pizarron_id} firmado por {$personal->nombre}", [
            'actor_tipo'     => 'personal',
            'actor_id'       => $personal->id,
            'actor_nombre'   => $personal->nombre,
            'documento_tipo' => 'solicitud',
            'documento_id'   => $solicitud->id,
        ]);

        // Marcar el reporte como concretado ahora que el jefe firmó
        if ($solicitud->reporte_pizarron_id) {
            \App\Models\ReportePizarron::where('id', $solicitud->reporte_pizarron_id)
                ->update(['concretado' => true, 'concretado_at' => now()]);
        }

        return back()->with('firmado_id', $solicitud->id);
    }

    public function pendientesCount()
    {
        $personal = Auth::guard('personal')->user();
        abort_unless($personal->es_jefe_servicio, 403);

        $total = Registro::where('jefe_id', $personal->id)->where('estado', 'en_firma')->count()
            + \App\Models\FirmaSolicitud::where('personal_reportante_id', $personal->id)->where('estado', 'pendiente')->count()
            + \App\Models\ValeInventario::where('jefe_id', $personal->id)->where('estado', 'en_firma')->count();

        return response()->json(['total' => $total]);
    }

    public function portalValePdf(\App\Models\ValeInventario $vale)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($vale->jefe_id === $personal->id, 403);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.vale-inventario-preview', compact('vale'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('vale-preview-' . $vale->id . '.pdf');
    }

    public function showFirmarVale(\App\Models\ValeInventario $vale)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($vale->jefe_id === $personal->id, 403);

        return view('portal.firmar-vale', compact('vale', 'personal'));
    }

    public function firmarValePortal(Request $request, \App\Models\ValeInventario $vale)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($vale->jefe_id === $personal->id, 403);
        abort_unless($vale->estado === 'en_firma', 400);

        $request->validate([
            'firma_posicion' => 'required|string',
        ], [
            'firma_posicion.required' => 'Coloca tu firma en el documento antes de confirmar.',
        ]);

        $firmaData = $request->input('firma_data') ?: $personal->firma;

        if (! $firmaData) {
            return back()->withErrors(['firma' => 'No tienes firma registrada.']);
        }

        $vale->update([
            'estado'       => 'culminado',
            'firmado_at'   => now(),
            'firma_imagen' => $firmaData,
        ]);

        AuditService::log('firma', "Vale #{$vale->id} firmado: {$vale->equipo_nombre} — {$vale->area}", [
            'actor_tipo'     => 'personal',
            'actor_id'       => $personal->id,
            'actor_nombre'   => $personal->nombre,
            'documento_tipo' => 'vale',
            'documento_id'   => $vale->id,
        ]);

        return redirect()->route('portal.firmas')->with('vale_confirmado', $vale->id);
    }

    public function firmasUpdated()
    {
        $personal = Auth::guard('personal')->user();
        abort_unless($personal->es_jefe_servicio, 403);

        $tsReg = Registro::where('jefe_id', $personal->id)
            ->whereIn('estado', ['en_firma', 'culminado'])
            ->max('updated_at');

        $tsSol = \App\Models\FirmaSolicitud::where('personal_reportante_id', $personal->id)
            ->max('updated_at');

        $ts = max($tsReg, $tsSol);

        return response()->json(['updated_at' => $ts]);
    }

    public function showFirmarSolicitud(\App\Models\FirmaSolicitud $solicitud)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($solicitud->personal_reportante_id === $personal->id, 403);

        return view('portal.firmar-solicitud', compact('solicitud', 'personal'));
    }


    public function showFirmarRegistro(Registro $registro)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($registro->jefe_id === $personal->id, 403);

        $registro->load(['formato', 'usuario']);

        return view('portal.firmar-registro', compact('registro', 'personal'));
    }

    public function firmarRegistro(Request $request, Registro $registro)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($registro->jefe_id === $personal->id, 403);
        abort_unless($registro->estado === 'en_firma', 400);

        $request->validate([
            'firma_posicion' => 'required|string',
        ], [
            'firma_posicion.required' => 'Coloca tu firma en el documento antes de confirmar.',
        ]);

        $registro->update([
            'estado'          => 'culminado',
            'firmado_at'      => now(),
            'firma_jefe_data' => $request->firma_posicion,
        ]);

        AuditService::log('firma', "Registro #{$registro->id} firmado por {$personal->nombre}", [
            'actor_tipo'     => 'personal',
            'actor_id'       => $personal->id,
            'actor_nombre'   => $personal->nombre,
            'documento_tipo' => 'registro',
            'documento_id'   => $registro->id,
        ]);

        return redirect()->route('portal.firmas')->with('firmado_id', $registro->id);
    }

    public function registroPdf(Registro $registro)
    {
        $personal = Auth::guard('personal')->user();

        abort_unless($registro->jefe_id === $personal->id, 403);

        $formato = $registro->formato;
        abort_unless($formato && Storage::disk('local')->exists($formato->archivo_path), 404);

        $registro->load('formato');
        $pdf = (new \App\Services\RegistroOverlayService())->stream($registro);
        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-cache']);
    }

    public function registroPdfTemplate(Registro $registro)
    {
        $personal = Auth::guard('personal')->user();
        abort_unless($registro->jefe_id === $personal->id, 403);
        $formato = $registro->formato;
        abort_unless($formato && Storage::disk('local')->exists($formato->archivo_path), 404);

        $path = Storage::disk('local')->path($formato->archivo_path);
        return response()->file($path, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-cache']);
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

        $pdf = (new \App\Services\BitacoraOverlayService())->stream($bitacora);
        return response($pdf, 200, ['Content-Type' => 'application/pdf']);
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
            'descripcion.required' => 'Escribe el mensaje antes de enviar.',
            'descripcion.min'      => 'Por favor da un poco más de detalle (mínimo 10 caracteres).',
        ]);

        $areaDepartamento = $personal->servicio;

        // La IA extrae todos los campos estructurados del mensaje libre
        $datos = $gemini->extraerDatosReporte($request->descripcion, $areaDepartamento);

        $titulo = $datos['nombre_dispositivo']
            ? $datos['nombre_dispositivo'] . ' — ' . $areaDepartamento
            : 'Reporte — ' . $areaDepartamento;

        ReportePizarron::create([
            'titulo'                 => $titulo,
            'equipo'                 => $datos['nombre_dispositivo'],
            'ubicacion'              => $datos['ubicacion'],
            'descripcion'            => $request->descripcion,
            'descripcion_original'   => $request->descripcion,
            'prioridad'              => $datos['prioridad'] ?? 'baja',
            'estado'                 => 'pendiente',
            'minimizado'             => false,
            'personal_reportante_id' => $personal->id,
            'reportante_nombre'      => $personal->nombre,
            'reportante_servicio'    => $areaDepartamento,
            'nombre_dispositivo'     => $datos['nombre_dispositivo'],
            'marca'                  => $datos['marca'],
            'modelo'                 => $datos['modelo'],
            'numero_serie'           => $datos['numero_serie'],
            'numero_control'         => $datos['numero_control'],
            'tipo_servicio'          => $datos['tipo_servicio'],
            'tipo_baja'              => $datos['tipo_baja'],
        ]);

        return back()->with('enviado', true);
    }
}
