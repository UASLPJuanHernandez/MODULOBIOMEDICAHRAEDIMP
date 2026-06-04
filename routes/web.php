<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleLoginController;
use App\Http\Controllers\PortalReportesController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de debugging eliminadas (dashboard-debug, test-dashboard, test-admin-access, test-login) - limpieza post migración a widgets definitivos

// Login simplificado
Route::get('/simple-login', [SimpleLoginController::class, 'showLoginForm'])->name('simple.login');
Route::post('/simple-login', [SimpleLoginController::class, 'login'])->name('simple.login.submit');
Route::post('/simple-logout', [SimpleLoginController::class, 'logout'])->name('simple.logout');

// Redirigir login principal al simplificado
Route::get('/biomedica/login', function() {
    return redirect('/simple-login');
});

// Ruta para generar vale PDF de mantenimiento
Route::get('/mantenimiento/{mantenimiento}/vale-pdf', [App\Http\Controllers\MantenimientoController::class, 'generarValePDF'])
    ->name('mantenimiento.vale.pdf')
    ->middleware('auth');


// Pizarrón en pantalla completa (sin layout de Filament)
Route::get('/pizarron', function () {
    $reportes = \App\Models\ReportePizarron::activos()
        ->orderByRaw("CASE prioridad WHEN 'urgencia' THEN 1 WHEN 'moderada' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
        ->orderBy('created_at', 'desc')
        ->get();
    $eventos  = \App\Models\EventoCalendario::query()
        ->whereDate('fecha_inicio', '>=', now()->startOfMonth())
        ->whereDate('fecha_inicio', '<=', now()->endOfMonth()->addMonth())
        ->orderBy('fecha_inicio')
        ->get();
    return view('pizarron-standalone', compact('reportes', 'eventos'));
})->name('pizarron.standalone')->middleware('auth');

// API: conteo de reportes activos para el ciclo JS
Route::get('/pizarron/count', function () {
    return response()->json(['count' => \App\Models\ReportePizarron::activos()->count()]);
})->middleware('auth');

// API: prioridad máxima de reportes creados en los últimos 15 seg (para sonido)
Route::get('/pizarron/nuevos', function () {
    $orden = ['baja' => 1, 'media' => 2, 'moderada' => 3, 'urgencia' => 4];
    $nuevos = \App\Models\ReportePizarron::activos()
        ->where('created_at', '>=', now()->subSeconds(15))
        ->pluck('prioridad');
    $maxPrioridad = $nuevos->sortByDesc(fn($p) => $orden[$p] ?? 0)->first();
    return response()->json(['prioridad' => $maxPrioridad]);
})->middleware('auth');

// API: timestamp del último cambio para detectar ediciones
Route::get('/pizarron/updated', function () {
    $ts = \App\Models\ReportePizarron::activos()->max('updated_at');
    return response()->json(['updated_at' => $ts]);
})->middleware('auth');

// API: timestamp del último evento para detectar nuevos o ediciones
Route::get('/pizarron/eventos-updated', function () {
    $ts = \App\Models\EventoCalendario::whereDate('fecha_inicio', '>=', now()->startOfMonth())
        ->whereDate('fecha_inicio', '<=', now()->endOfMonth()->addMonth())
        ->max('updated_at');
    return response()->json(['updated_at' => $ts]);
})->middleware('auth');

// API: eventos del calendario para la vista standalone
Route::get('/pizarron/eventos', function () {
    $start = request('start');
    $end   = request('end');
    $eventos = \App\Models\EventoCalendario::query()
        ->when($start, fn($q) => $q->where('fecha_inicio', '>=', $start))
        ->when($end,   fn($q) => $q->where('fecha_inicio', '<=', $end))
        ->get()
        ->map(fn($e) => [
            'id'              => $e->id,
            'title'           => $e->titulo,
            'start'           => $e->fecha_inicio,
            'end'             => $e->fecha_fin,
            'allDay'          => $e->todo_el_dia,
            'backgroundColor' => $e->color ?? '#3b82f6',
            'borderColor'     => $e->color ?? '#3b82f6',
        ]);
    return response()->json($eventos);
})->middleware('auth');

// Rutas para Filament Admin
Route::get('/biomedica/vales/create-from-movimiento/{movimiento_id}', function ($movimiento_id) {
    return redirect()->route('filament.admin.resources.vales.create', [
        'movimiento_id' => $movimiento_id
    ]);
})->name('admin.vales.create-from-movimiento');

// Vales DOCX del inventario
Route::middleware('auth')->group(function () {
    Route::get('/inventario-equipo/{equipo}/vale-entrega', [App\Http\Controllers\InventarioEquipoController::class, 'valeEntregaDocx'])
        ->name('inventario.equipo.vale-entrega');
    Route::get('/inventario-equipo/{equipo}/vale-retiro', [App\Http\Controllers\InventarioEquipoController::class, 'valeRetiroDocx'])
        ->name('inventario.equipo.vale-retiro');
    Route::get('/vale-inventario/{vale}/redescargar', [App\Http\Controllers\InventarioEquipoController::class, 'valeRedescargar'])
        ->name('inventario.vale.redescargar');
    Route::get('/vale-inventario/{vale}/preview', [App\Http\Controllers\InventarioEquipoController::class, 'valePreview'])
        ->name('inventario.vale.preview');
    Route::get('/vale-inventario/{vale}/descargar-pdf', [App\Http\Controllers\InventarioEquipoController::class, 'valeDescargarPdf'])
        ->name('inventario.vale.descargar-pdf');
    Route::post('/vale-inventario/{vale}/concretar', [App\Http\Controllers\InventarioEquipoController::class, 'concretarVale'])
        ->name('inventario.vale.concretar');
    Route::post('/vale-inventario/{vale}/firmar-jefe', [App\Http\Controllers\InventarioEquipoController::class, 'firmarValeJefe'])
        ->name('inventario.vale.firmar-jefe');

    Route::get('/bitacora/{bitacora}/descargar', function (\App\Models\BitacoraReporte $bitacora) {
        $service = new \App\Services\BitacoraOverlayService();
        $path    = $service->generar($bitacora);
        return response()->download($path, 'SolicitudServicio_' . $bitacora->id . '.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    })->name('bitacora.descargar');

    Route::get('/bitacora/{bitacora}/preview', function (\App\Models\BitacoraReporte $bitacora) {
        $pdf = (new \App\Services\BitacoraOverlayService())->stream($bitacora);
        return response($pdf, 200, ['Content-Type' => 'application/pdf']);
    })->name('bitacora.preview');

    Route::get('/bitacora/{bitacora}/debug', function (\App\Models\BitacoraReporte $bitacora) {
        $pdf = (new \App\Services\BitacoraOverlayService())->debug($bitacora);
        return response($pdf, 200, ['Content-Type' => 'application/pdf']);
    })->name('bitacora.debug');

});

// Historial de cambios del inventario de equipos (PDF individual)
Route::get('/inventario-equipo/{equipo}/historial-pdf', [App\Http\Controllers\InventarioEquipoController::class, 'historialPdf'])
    ->name('inventario.equipo.historial.pdf')
    ->middleware('auth');

// ── Portal de Reportes (personal del hospital) ────────────────────────────
Route::prefix('reportes')->name('portal.')->group(function () {
    // Rutas públicas (login y registro)
    Route::get('/',      [PortalReportesController::class, 'showLogin'])->name('login');
    Route::get('/login', fn() => redirect('/reportes'));
    Route::post('/login',[PortalReportesController::class, 'login'])->name('login.submit');
    Route::get('/registro', [PortalReportesController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',[PortalReportesController::class, 'registro'])->name('registro.submit');
    Route::post('/logout',  [PortalReportesController::class, 'logout'])->name('logout');

    // Rutas protegidas (solo personal aprobado)
    Route::middleware('auth:personal')->group(function () {
        Route::get('/enviar',  [PortalReportesController::class, 'showForm'])->name('reportes.form');
        Route::post('/enviar', [PortalReportesController::class, 'enviar'])->name('reportes.enviar');
        Route::get('/firmas',                            [PortalReportesController::class, 'showFirmas'])->name('firmas');
        Route::get('/firmas/updated',                    [PortalReportesController::class, 'firmasUpdated'])->name('firmas.updated');
        Route::get('/firmas/pendientes',                 [PortalReportesController::class, 'pendientesCount'])->name('firmas.pendientes');
        Route::get('/firmas/{solicitud}/ver',            [PortalReportesController::class, 'showFirmarSolicitud'])->name('firmas.ver');
        Route::post('/firmas/{solicitud}/firmar',        [PortalReportesController::class, 'firmar'])->name('firmar');
        Route::get('/bitacora/{bitacora}/pdf',           [PortalReportesController::class, 'portalBitacoraPdf'])->name('bitacora.pdf');
        Route::get('/documentos/{registro}/ver',         [PortalReportesController::class, 'showFirmarRegistro'])->name('documentos.ver');
        Route::post('/documentos/{registro}/firmar',     [PortalReportesController::class, 'firmarRegistro'])->name('documentos.firmar');
        Route::get('/documentos/{registro}/pdf',         [PortalReportesController::class, 'registroPdf'])->name('documentos.pdf');
        Route::get('/documentos/{registro}/template',   [PortalReportesController::class, 'registroPdfTemplate'])->name('documentos.template');
        Route::post('/vales/{vale}/confirmar',           [PortalReportesController::class, 'confirmarVale'])->name('vales.confirmar');
        Route::get('/vales/{vale}/pdf',                  [PortalReportesController::class, 'portalValePdf'])->name('vales.pdf');
        Route::get('/vales/{vale}/ver',                  [PortalReportesController::class, 'showFirmarVale'])->name('vales.ver');
        Route::post('/vales/{vale}/firmar',              [PortalReportesController::class, 'firmarValePortal'])->name('vales.firmar');
    });
});

// PDF de vale de consumible
Route::get('/biomedica/consumible-vale-pdf/{vale}', function (\App\Models\ValeInventario $vale) {
    abort_unless(auth()->check() && $vale->consumible_id, 403);
    $c   = $vale->consumible;
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.consumible-vale', [
        'fecha'          => $vale->created_at->format('d/m/Y'),
        'nombre'         => $vale->equipo_nombre,
        'descripcion'    => $c?->descripcion ?? '',
        'marca'          => $vale->marca      ?? '',
        'referencia'     => $vale->modelo     ?? '',
        'cantidad'       => $vale->cantidad_entregada ?? '—',
        'nombre_entrega' => $vale->nombre_entrega ?? '',
        'cargo_entrega'  => $vale->cargo_entrega  ?? '',
        'nombre_recibe'  => $vale->quien_recibe  ?? '',
        'cargo_recibe'   => $vale->cargo_recibe  ?? '',
        'observaciones'  => $vale->observaciones ?? '',
    ])->setPaper('letter', 'portrait');
    return response()->streamDownload(
        fn () => print($pdf->output()),
        'Vale_Consumible_' . $vale->id . '.pdf',
        ['Content-Type' => 'application/pdf']
    );
})->name('admin.consumible.vale.pdf')->middleware('auth');

// Exportación PDF de estadísticas de ingeniería
Route::get('/biomedica/estadisticas-ingenieria/exportar-pdf', [App\Http\Controllers\EstadisticasIngenieriaController::class, 'exportarPdf'])
    ->name('admin.estadisticas-ingenieria.pdf')
    ->middleware('auth');

// Exportación PDF de auditoría (solo admins autenticados)
Route::get('/biomedica/auditoria/exportar-pdf', [App\Http\Controllers\AuditoriaController::class, 'exportarPdf'])
    ->name('admin.auditoria.pdf')
    ->middleware('auth');

// Historial general del inventario (PDF global con filtros)
Route::get('/inventario/historial-general-pdf', [App\Http\Controllers\InventarioEquipoController::class, 'historialGeneralPdf'])
    ->name('inventario.historial-general.pdf')
    ->middleware('auth');

// Servir archivos de formatos (docx/pdf)
Route::get('/formato-archivo/{formato}', function (\App\Models\Formato $formato) {
    $disk = \Illuminate\Support\Facades\Storage::disk('local');
    abort_unless($disk->exists($formato->archivo_path), 404);

    $ext  = strtolower(pathinfo($formato->archivo_original, PATHINFO_EXTENSION));
    $mime = match($ext) {
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        default => 'application/octet-stream',
    };

    return response($disk->get($formato->archivo_path), 200, [
        'Content-Type'        => $mime,
        'Content-Disposition' => ($ext === 'pdf' ? 'inline' : 'attachment')
                                 . '; filename="' . rawurlencode($formato->archivo_original) . '"',
        'X-Frame-Options'     => 'SAMEORIGIN',
        'Cache-Control'       => 'private, no-cache',
    ]);
})->name('formato.archivo')->middleware('auth');

// ── Overlay Editor (herramienta de desarrollo) ──────────────────────────────
Route::get('/overlay-editor', function () {
    $positions  = json_decode(file_get_contents(storage_path('app/overlay_positions.json')), true);
    $bitacora   = \App\Models\BitacoraReporte::latest()->first();
    $bitacoraId = $bitacora?->id ?? 1;
    return view('overlay-editor', compact('positions', 'bitacoraId'));
})->name('overlay.editor');

Route::get('/overlay-template', function () {
    return response()->file(storage_path('app/templates/solicitud_servicio.pdf'), [
        'Content-Type'  => 'application/pdf',
        'Cache-Control' => 'no-cache',
    ]);
})->name('overlay.template');

Route::post('/overlay-editor/save', function (\Illuminate\Http\Request $request) {
    try {
        $data = $request->json()->all();
        file_put_contents(
            storage_path('app/overlay_positions.json'),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
    }
})->name('overlay.save');
