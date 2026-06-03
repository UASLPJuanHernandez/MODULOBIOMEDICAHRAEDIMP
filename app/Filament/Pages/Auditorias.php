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

        // ── Stats filtrados ───────────────────────────────────────────────────
        $desde = $this->fechaDesde ?: null;
        $hasta = $this->fechaHasta ?: null;

        $valesFirmados = ValeInventario::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->count();

        $registrosFirmados = Registro::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->count();

        $reportesFirmados = FirmaSolicitud::whereNotNull('firmado_at')
            ->when($desde, fn ($q) => $q->whereDate('firmado_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('firmado_at', '<=', $hasta))
            ->count();

        $totalFirmas = $valesFirmados + $registrosFirmados + $reportesFirmados;

        $totalVales = ValeInventario::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        $totalRegistros = Registro::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        $totalReportes = FirmaSolicitud::query()
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        $usuariosActivos = PersonalReportante::where('estado', 'aprobado')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        $pendientes = PersonalReportante::where('estado', 'pendiente')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->count();

        // ── Firmas combinadas (para tab Firmas) ──────────────────────────────
        $fValeQ = ValeInventario::whereNotNull('firmado_at')
            ->with('jefe:id,nombre,area_jefe_servicio')
            ->latest('firmado_at');
        if ($this->fechaDesde !== '') $fValeQ->whereDate('firmado_at', '>=', $this->fechaDesde);
        if ($this->fechaHasta !== '') $fValeQ->whereDate('firmado_at', '<=', $this->fechaHasta);

        $fVale = $fValeQ->limit(200)
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','jefe_id','firmado_at','estado'])
            ->map(fn ($v) => [
                'tipo_doc'  => $v->tipo === 'entrega' ? 'Vale entrega' : 'Vale retiro',
                'documento' => ($v->equipo_nombre ?: '—') . ($v->numero_inventario ? " ({$v->numero_inventario})" : ''),
                'firmante'  => $v->jefe?->nombre ?? '—',
                'area'      => $v->area ?? '—',
                'fecha'     => $v->firmado_at,
                'badge'     => 'vale',
            ]);

        $fSolQ = FirmaSolicitud::whereNotNull('firmado_at')->latest('firmado_at');
        if ($this->fechaDesde !== '') $fSolQ->whereDate('firmado_at', '>=', $this->fechaDesde);
        if ($this->fechaHasta !== '') $fSolQ->whereDate('firmado_at', '<=', $this->fechaHasta);

        $fSol = $fSolQ->limit(200)->get()
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

        $fRegQ = Registro::whereNotNull('firmado_at')
            ->with(['jefe:id,nombre,area_jefe_servicio', 'formato:id,nombre'])
            ->latest('firmado_at');
        if ($this->fechaDesde !== '') $fRegQ->whereDate('firmado_at', '>=', $this->fechaDesde);
        if ($this->fechaHasta !== '') $fRegQ->whereDate('firmado_at', '<=', $this->fechaHasta);

        $fReg = $fRegQ->limit(200)->get()
            ->map(fn ($r) => [
                'tipo_doc'  => 'Registro',
                'documento' => $r->formato?->nombre ?? 'Registro #' . $r->id,
                'firmante'  => $r->jefe?->nombre ?? '—',
                'area'      => $r->jefe?->area_jefe_servicio ?? '—',
                'fecha'     => $r->firmado_at,
                'badge'     => 'registro',
            ]);

        $todasFirmas = $fVale->concat($fSol)->concat($fReg)
            ->sortByDesc('fecha')
            ->when($this->busqueda !== '', fn ($c) => $c->filter(function ($f) {
                $q = strtolower($this->busqueda);
                return str_contains(strtolower($f['firmante']), $q)
                    || str_contains(strtolower($f['area']), $q)
                    || str_contains(strtolower($f['documento']), $q)
                    || str_contains(strtolower($f['tipo_doc']), $q);
            }))
            ->values();

        // ── Vales ────────────────────────────────────────────────────────────
        $valesQuery = ValeInventario::with('jefe:id,nombre')->latest();

        if ($this->fechaDesde !== '') {
            $valesQuery->whereDate('created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $valesQuery->whereDate('created_at', '<=', $this->fechaHasta);
        }

        $vales = $valesQuery->limit(300)
            ->get(['id','tipo','equipo_nombre','numero_inventario','area','usuario_nombre','jefe_id','estado','created_at','firmado_at']);

        // ── Usuarios ─────────────────────────────────────────────────────────
        $usuariosQuery = PersonalReportante::latest();
        if ($this->fechaDesde !== '') {
            $usuariosQuery->whereDate('created_at', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta !== '') {
            $usuariosQuery->whereDate('created_at', '<=', $this->fechaHasta);
        }
        if ($this->busqueda !== '') {
            $q = $this->busqueda;
            $usuariosQuery->where(fn ($sq) => $sq
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('servicio', 'like', "%{$q}%")
                ->orWhere('area_jefe_servicio', 'like', "%{$q}%")
                ->orWhere('numero_empleado', 'like', "%{$q}%")
            );
        }
        $usuarios = $usuariosQuery
            ->get(['id','nombre','servicio','es_jefe_servicio','area_jefe_servicio','estado','created_at']);

        return compact(
            'totalFirmas',
            'valesFirmados', 'totalVales',
            'registrosFirmados', 'totalRegistros',
            'reportesFirmados', 'totalReportes',
            'usuariosActivos', 'pendientes',
            'auditLogs', 'historialEquipos',
            'todasFirmas', 'vales', 'usuarios'
        );
    }
}
