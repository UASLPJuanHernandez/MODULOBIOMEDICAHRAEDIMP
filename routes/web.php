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
Route::get('/admin/login', function() {
    return redirect('/simple-login');
});

// Ruta para generar vale PDF de mantenimiento
Route::get('/mantenimiento/{mantenimiento}/vale-pdf', [App\Http\Controllers\MantenimientoController::class, 'generarValePDF'])
    ->name('mantenimiento.vale.pdf')
    ->middleware('auth');


// Pizarrón en pantalla completa (sin layout de Filament)
Route::get('/pizarron', function () {
    $reportes = \App\Models\ReportePizarron::activos()->orderBy('created_at', 'asc')->get();
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
Route::get('/admin/vales/create-from-movimiento/{movimiento_id}', function ($movimiento_id) {
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

    Route::get('/bitacora/{bitacora}/descargar', function (\App\Models\BitacoraReporte $bitacora) {
        $service = new \App\Services\BitacoraDocxService();
        $path    = $service->generar($bitacora);
        return response()->download($path, 'Bitacora_' . $bitacora->id . '.docx')
            ->deleteFileAfterSend(true);
    })->name('bitacora.descargar');

    Route::get('/bitacora/{bitacora}/preview', function (\App\Models\BitacoraReporte $bitacora) {
        $meses = [
            1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
            7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre',
        ];
        $mesEspanol = $meses[\Carbon\Carbon::parse($bitacora->fecha_reporte)->month];

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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bitacora', compact(
            'bitacora', 'mesEspanol', 'textoResultado', 'labelResultado'
        ))->setPaper('letter', 'portrait');

        return $pdf->stream('Bitacora_' . $bitacora->id . '.pdf');
    })->name('bitacora.preview');
});

// Historial de cambios del inventario de equipos (PDF individual)
Route::get('/inventario-equipo/{equipo}/historial-pdf', [App\Http\Controllers\InventarioEquipoController::class, 'historialPdf'])
    ->name('inventario.equipo.historial.pdf')
    ->middleware('auth');

// ── Portal de Reportes (personal del hospital) ────────────────────────────
Route::prefix('reportes')->name('portal.')->group(function () {
    // Rutas públicas (login y registro)
    Route::get('/',         [PortalReportesController::class, 'showLogin'])->name('login');
    Route::post('/login',   [PortalReportesController::class, 'login'])->name('login.submit');
    Route::get('/registro', [PortalReportesController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',[PortalReportesController::class, 'registro'])->name('registro.submit');
    Route::post('/logout',  [PortalReportesController::class, 'logout'])->name('logout');

    // Rutas protegidas (solo personal aprobado)
    Route::middleware('auth:personal')->group(function () {
        Route::get('/enviar',  [PortalReportesController::class, 'showForm'])->name('reportes.form');
        Route::post('/enviar', [PortalReportesController::class, 'enviar'])->name('reportes.enviar');
        Route::get('/firmas',                            [PortalReportesController::class, 'showFirmas'])->name('firmas');
        Route::get('/firmas/{solicitud}/ver',            [PortalReportesController::class, 'showFirmarSolicitud'])->name('firmas.ver');
        Route::post('/firmas/{solicitud}/firmar',        [PortalReportesController::class, 'firmar'])->name('firmar');
        Route::get('/bitacora/{bitacora}/pdf',           [PortalReportesController::class, 'portalBitacoraPdf'])->name('bitacora.pdf');
    });
});

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
