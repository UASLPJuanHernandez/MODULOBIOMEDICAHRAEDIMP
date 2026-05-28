<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\FirmaSolicitud;
use App\Models\InventarioEquipoHistorial;
use App\Models\PersonalReportante;
use App\Models\Registro;
use App\Models\ValeInventario;
use Filament\Actions\Action;
use Filament\Pages\Page;

class Auditorias extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Auditorías';
    protected static ?string $modelLabel      = 'Auditoría';
    protected static ?int    $navigationSort  = 90;
    protected static string  $view            = 'filament.pages.auditorias';

    // ── Filtros reactivos (Livewire) ─────────────────────────────────────────
    public string $busqueda    = '';
    public string $filtroTipo  = '';
    public string $fechaDesde  = '';
    public string $fechaHasta  = '';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->url(fn () => route('admin.auditoria.pdf', array_filter([
                    'desde' => $this->fechaDesde,
                    'hasta' => $this->fechaHasta,
                    'tipo'  => $this->filtroTipo,
                ])))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        $hoy  = today();
        $mes  = now()->startOfMonth();
        $sem  = now()->startOfWeek();

        // ── Filtros aplicados a audit_logs ───────────────────────────────────
        $logQuery = AuditLog::query();

        if ($this->busqueda !== '') {
            $q = $this->busqueda;
            $logQuery->where(fn ($sq) => $sq
                ->where('descripcion', 'like', "%{$q}%")
                ->orWhere('actor_nombre', 'like', "%{$q}%")
                ->orWhere('ip', 'like', "%{$q}%")
            );
        }
        if ($this->filtroTipo !== '') {
            $logQuery->where('tipo', $this->filtroTipo);
        }
        if ($this->fechaDesde !== '') {
            $logQuery->whereDate('created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $logQuery->whereDate('created_at', '<=', $this->fechaHasta);
        }

        $auditLogs = $logQuery->latest()->limit(500)->get();

        // ── Historial de equipos (con filtros de fecha y búsqueda) ───────────
        $histQuery = InventarioEquipoHistorial::with('inventarioEquipo:id,numero_inventario,equipo,area')
            ->latest();

        if ($this->fechaDesde !== '') {
            $histQuery->whereDate('created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $histQuery->whereDate('created_at', '<=', $this->fechaHasta);
        }
        if ($this->busqueda !== '') {
            $q = $this->busqueda;
            $histQuery->where(fn ($sq) => $sq
                ->where('descripcion', 'like', "%{$q}%")
                ->orWhere('usuario_nombre', 'like', "%{$q}%")
            );
        }

        $historialEquipos = $histQuery->limit(400)->get();

        // ── Stats (sin filtros — reflejan el estado global del sistema) ───────
        $firmasHoy   = ValeInventario::whereDate('firmado_at', $hoy)->count()
            + FirmaSolicitud::whereDate('firmado_at', $hoy)->count()
            + Registro::whereDate('firmado_at', $hoy)->count();

        $firmasSem   = ValeInventario::where('firmado_at', '>=', $sem)->count()
            + FirmaSolicitud::where('firmado_at', '>=', $sem)->count()
            + Registro::where('firmado_at', '>=', $sem)->count();

        $valesMes        = ValeInventario::where('created_at', '>=', $mes)->count();
        $valesEnProceso  = ValeInventario::where('estado', 'en_firma')->count();
        $usuariosActivos = PersonalReportante::where('estado', 'aprobado')->count();
        $pendientes      = PersonalReportante::where('estado', 'pendiente')->count();

        // ── Firmas combinadas (para tab Firmas) ──────────────────────────────
        $fVale = ValeInventario::whereNotNull('firmado_at')
            ->with('jefe:id,nombre,area_jefe_servicio')
            ->latest('firmado_at')->limit(200)
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','jefe_id','firmado_at','estado'])
            ->map(fn ($v) => [
                'tipo_doc'  => $v->tipo === 'entrega' ? 'Vale entrega' : 'Vale retiro',
                'documento' => ($v->equipo_nombre ?: '—') . ($v->numero_inventario ? " ({$v->numero_inventario})" : ''),
                'firmante'  => $v->jefe?->nombre ?? '—',
                'area'      => $v->area ?? '—',
                'fecha'     => $v->firmado_at,
                'badge'     => 'vale',
            ]);

        $fSol = FirmaSolicitud::whereNotNull('firmado_at')
            ->latest('firmado_at')->limit(200)->get()
            ->map(function ($s) {
                $jefe = PersonalReportante::find($s->personal_reportante_id);
                return [
                    'tipo_doc'  => 'Reporte',
                    'documento' => 'Reporte #' . $s->reporte_pizarron_id,
                    'firmante'  => $jefe?->nombre ?? '—',
                    'area'      => $jefe?->area_jefe_servicio ?? '—',
                    'fecha'     => $s->firmado_at,
                    'badge'     => 'reporte',
                ];
            });

        $fReg = Registro::whereNotNull('firmado_at')
            ->with(['jefe:id,nombre,area_jefe_servicio', 'formato:id,nombre'])
            ->latest('firmado_at')->limit(200)->get()
            ->map(fn ($r) => [
                'tipo_doc'  => 'Registro',
                'documento' => $r->formato?->nombre ?? 'Registro #' . $r->id,
                'firmante'  => $r->jefe?->nombre ?? '—',
                'area'      => $r->jefe?->area_jefe_servicio ?? '—',
                'fecha'     => $r->firmado_at,
                'badge'     => 'registro',
            ]);

        $todasFirmas = $fVale->concat($fSol)->concat($fReg)
            ->sortByDesc('fecha')->values();

        // ── Vales ────────────────────────────────────────────────────────────
        $vales = ValeInventario::with('jefe:id,nombre')
            ->latest()->limit(300)
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','usuario_nombre','jefe_id','estado','created_at','firmado_at']);

        // ── Usuarios ─────────────────────────────────────────────────────────
        $usuarios = PersonalReportante::latest()
            ->get(['id','nombre','servicio','es_jefe_servicio','area_jefe_servicio','estado','created_at']);

        return compact(
            'firmasHoy', 'firmasSem', 'valesMes', 'valesEnProceso',
            'usuariosActivos', 'pendientes',
            'auditLogs', 'historialEquipos',
            'todasFirmas', 'vales', 'usuarios'
        );
    }
}
