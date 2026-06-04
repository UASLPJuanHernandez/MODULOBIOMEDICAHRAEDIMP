<?php

namespace App\Filament\Pages;

use App\Models\BitacoraReporte;
use App\Models\FirmaSolicitud;
use App\Models\Formato;
use App\Models\Ingeniero;
use App\Models\PersonalReportante;
use App\Models\Registro;
use App\Models\ReportePizarron;
use App\Filament\Resources\ValeInventarioResource;
use App\Models\ValeInventario;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class DocumentosGenerados extends Page
{
    protected static string  $view            = 'filament.pages.documentos-generados';
    protected static ?string $navigationIcon  = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Documentos generados';
    protected static ?string $title           = 'Documentos generados';
    protected static ?int    $navigationSort  = 3;

    // ---------------------------------------------------------------
    // Estado de vista: 'lista' | 'ver'
    // ---------------------------------------------------------------
    public string $vistaDoc         = 'lista';
    public ?int   $registroViendoId = null;


    // ---------------------------------------------------------------
    // Pestañas principales y filtros
    // ---------------------------------------------------------------
    public string $tabActiva = 'vales'; // 'vales' | 'bitacoras' | 'registros' | 'reportes'

    // Filtros — Vales
    public string $vFiltroTipo  = '';
    public string $vBusqueda    = '';
    public string $vFechaDesde  = '';
    public string $vFechaHasta  = '';

    // Filtros — Registros de formatos
    public string $rSubTab          = 'pendientes'; // 'pendientes' | 'culminado'
    public string $rFiltroFormato   = '';
    public string $rFiltroIngeniero = '';
    public string $rBusqueda        = '';
    public string $rFechaDesde      = '';
    public string $rFechaHasta      = '';

    // Filtros — Bitácoras
    public string $bBusqueda   = '';
    public string $bFechaDesde = '';
    public string $bFechaHasta = '';

    // Sub-pestaña de Reportes: 'pendientes' | 'completados'
    public string $rpSubTab    = 'pendientes';

    // Filtros — Reportes
    public string $rpBusqueda   = '';
    public string $rpFechaDesde = '';
    public string $rpFechaHasta = '';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return 'Documentos generados';
    }

    // ---------------------------------------------------------------
    // Cambio de pestaña
    // ---------------------------------------------------------------
    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = $tab;
    }

    public function cambiarSubTabReportes(string $sub): void
    {
        $this->rpSubTab   = $sub;
        $this->rpBusqueda = '';
        $this->rpFechaDesde = '';
        $this->rpFechaHasta = '';
    }

    public function cambiarSubTabRegistros(string $sub): void
    {
        $this->rSubTab          = $sub;
        $this->rFiltroFormato   = '';
        $this->rFiltroIngeniero = '';
        $this->rBusqueda        = '';
        $this->rFechaDesde      = '';
        $this->rFechaHasta      = '';
    }

    // ---------------------------------------------------------------
    // Datos para Vales
    // ---------------------------------------------------------------
    public function getVales()
    {
        $estados = match($this->vSubTab) {
            'culminados' => ['culminado'],
            default      => ['pendiente', 'en_firma'],
        };

        return ValeInventario::query()
            ->whereIn('estado', $estados)
            ->with('jefe')
            ->when($this->vFiltroTipo, fn ($q) => $q->where('tipo', $this->vFiltroTipo))
            ->when($this->vBusqueda, fn ($q) =>
                $q->where(function ($sub) {
                    $sub->where('numero_inventario', 'like', '%' . $this->vBusqueda . '%')
                        ->orWhere('equipo_nombre', 'like', '%' . $this->vBusqueda . '%')
                        ->orWhere('area', 'like', '%' . $this->vBusqueda . '%')
                        ->orWhere('usuario_nombre', 'like', '%' . $this->vBusqueda . '%');
                })
            )
            ->when($this->vFechaDesde, fn ($q) => $q->whereDate('created_at', '>=', $this->vFechaDesde))
            ->when($this->vFechaHasta, fn ($q) => $q->whereDate('created_at', '<=', $this->vFechaHasta))
            ->latest()
            ->paginate(25, pageName: 'valesPage');
    }

    public function limpiarFiltrosVales(): void
    {
        $this->vFiltroTipo = '';
        $this->vBusqueda   = '';
        $this->vFechaDesde = '';
        $this->vFechaHasta = '';
    }

    // ---------------------------------------------------------------
    // Datos para Registros de Formatos
    // ---------------------------------------------------------------
    public function getFormatos()
    {
        return Formato::orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getIngenieros()
    {
        return Ingeniero::activos()->orderBy('nombre')->get();
    }

    public function getRegistros()
    {
        $estados = $this->rSubTab === 'culminado'
            ? ['culminado']
            : ['pendiente', 'en_firma'];

        return Registro::query()
            ->where('es_borrador', false)
            ->whereIn('estado', $estados)
            ->with(['formato:id,nombre', 'usuario:id,name', 'firmadoPor:id,name', 'jefe:id,nombre'])
            ->when($this->rFiltroFormato, fn ($q) => $q->where('formato_id', $this->rFiltroFormato))
            ->when($this->rFiltroIngeniero, fn ($q) => $q->where('usuario_id', $this->rFiltroIngeniero))
            ->when($this->rBusqueda, fn ($q) =>
                $q->where('identificador', 'like', '%' . $this->rBusqueda . '%')
            )
            ->when($this->rFechaDesde, fn ($q) => $q->whereDate('created_at', '>=', $this->rFechaDesde))
            ->when($this->rFechaHasta, fn ($q) => $q->whereDate('created_at', '<=', $this->rFechaHasta))
            ->latest()
            ->paginate(25, pageName: 'regPage');
    }

    public function limpiarFiltrosRegistros(): void
    {
        $this->rFiltroFormato   = '';
        $this->rFiltroIngeniero = '';
        $this->rBusqueda        = '';
        $this->rFechaDesde      = '';
        $this->rFechaHasta      = '';
    }

    // ---------------------------------------------------------------
    // Badges para las pestañas (conteo hoy)
    // ---------------------------------------------------------------
    public function getBadgeVales(): int
    {
        return ValeInventario::where('estado', 'pendiente')->count();
    }

    public function getBadgeRegistros(): int
    {
        return Registro::where('es_borrador', false)->whereIn('estado', ['pendiente', 'en_firma'])->count();
    }

    public function getBadgeBitacoras(): int
    {
        return BitacoraReporte::whereDate('created_at', today())->count();
    }

    public function getBadgeReportesPendientes(): int
    {
        return ReportePizarron::where('estado', 'completado')
            ->where('concretado', false)
            ->count();
    }

    // ---------------------------------------------------------------
    // Datos para Bitácoras
    // ---------------------------------------------------------------
    public function getBitacoras()
    {
        return BitacoraReporte::query()
            ->when($this->bBusqueda, fn ($q) =>
                $q->where(function ($sub) {
                    $sub->where('nombre_personal',    'like', '%' . $this->bBusqueda . '%')
                        ->orWhere('area_departamento', 'like', '%' . $this->bBusqueda . '%')
                        ->orWhere('nombre_dispositivo','like', '%' . $this->bBusqueda . '%');
                })
            )
            ->when($this->bFechaDesde, fn ($q) => $q->whereDate('fecha_reporte', '>=', $this->bFechaDesde))
            ->when($this->bFechaHasta, fn ($q) => $q->whereDate('fecha_reporte', '<=', $this->bFechaHasta))
            ->latest()
            ->paginate(25, pageName: 'bitPage');
    }

    public function limpiarFiltrosBitacoras(): void
    {
        $this->bBusqueda   = '';
        $this->bFechaDesde = '';
        $this->bFechaHasta = '';
    }

    // ---------------------------------------------------------------
    // Datos para Reportes completados
    // ---------------------------------------------------------------
    public function getReportes()
    {
        $concretado = $this->rpSubTab === 'completados';

        return ReportePizarron::query()
            ->with(['bitacora', 'firmaSolicitud'])
            ->where('estado', 'completado')
            ->where('concretado', $concretado)
            ->when($this->rpBusqueda, fn ($q) =>
                $q->where(function ($sub) {
                    $sub->where('equipo',       'like', '%' . $this->rpBusqueda . '%')
                        ->orWhere('ubicacion',   'like', '%' . $this->rpBusqueda . '%')
                        ->orWhere('responsable', 'like', '%' . $this->rpBusqueda . '%')
                        ->orWhere('descripcion', 'like', '%' . $this->rpBusqueda . '%');
                })
            )
            ->when($this->rpFechaDesde, fn ($q) => $q->whereDate('updated_at', '>=', $this->rpFechaDesde))
            ->when($this->rpFechaHasta, fn ($q) => $q->whereDate('updated_at', '<=', $this->rpFechaHasta))
            ->latest('updated_at')
            ->paginate(25, pageName: 'rpPage');
    }

    public function limpiarFiltrosReportes(): void
    {
        $this->rpBusqueda   = '';
        $this->rpFechaDesde = '';
        $this->rpFechaHasta = '';
    }

    public function concretarReporte(int $id): void
    {
        ReportePizarron::where('id', $id)
            ->where('estado', 'completado')
            ->update([
                'concretado'    => true,
                'concretado_at' => now(),
            ]);
    }

    // ---------------------------------------------------------------
    // Modal: vista previa de vale de inventario
    // ---------------------------------------------------------------
    public ?int $valeViendoId = null;

    public function verVale(int $id): void
    {
        $this->valeViendoId = $id;
    }

    public function cerrarVale(): void
    {
        $this->valeViendoId = null;
    }

    public function getValeViendo(): ?ValeInventario
    {
        return $this->valeViendoId
            ? ValeInventario::find($this->valeViendoId)
            : null;
    }

    // ---------------------------------------------------------------
    // Modal: solicitar firma a un Jefe de Servicio
    // ---------------------------------------------------------------
    public ?int    $firmaModalReporteId  = null;
    public ?int    $firmaJefeId          = null;
    public ?string $firmaModalError      = null;

    public function abrirModalFirma(int $reporteId): void
    {
        $this->firmaModalReporteId = $reporteId;
        $this->firmaJefeId         = null;
        $this->firmaModalError     = null;
    }

    public function cerrarModalFirma(): void
    {
        $this->firmaModalReporteId = null;
        $this->firmaJefeId         = null;
        $this->firmaModalError     = null;
    }

    public function getJefesServicio()
    {
        return PersonalReportante::where('es_jefe_servicio', true)
            ->where('estado', 'aprobado')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'area_jefe_servicio']);
    }

    public function enviarSolicitudFirma(): void
    {
        if (! $this->firmaJefeId) {
            $this->firmaModalError = 'Selecciona un Jefe de Servicio.';
            return;
        }

        FirmaSolicitud::create([
            'reporte_pizarron_id'    => $this->firmaModalReporteId,
            'personal_reportante_id' => $this->firmaJefeId,
            'estado'                 => 'pendiente',
        ]);

        $this->cerrarModalFirma();
    }

    public function abrirBitacoraReporte(int $reporteId): mixed
    {
        $reporte  = ReportePizarron::findOrFail($reporteId);

        $personal = $reporte->personal_reportante_id
            ? \App\Models\PersonalReportante::find($reporte->personal_reportante_id)
            : null;

        $area = ($personal && $personal->es_jefe_servicio)
            ? $personal->area_jefe_servicio
            : null;

        $bitacora = \App\Models\BitacoraReporte::firstOrCreate(
            ['reporte_pizarron_id' => $reporteId],
            [
                'nombre_personal'   => $reporte->reportante_nombre ?? '',
                'area_departamento' => $area,
                'mensaje_original'  => $reporte->descripcion ?? '',
                'atiende_nombre'    => $reporte->responsable ?? '',
                'fecha_reporte'     => now()->toDateString(),
                'hora_reporte'      => now()->format('H:i'),
                'resultado'         => 'satisfactoria',
            ]
        );

        return redirect(\App\Filament\Resources\BitacoraReporteResource::getUrl('edit', ['record' => $bitacora]));
    }

    // ---------------------------------------------------------------
    // Vista previa de bitácora (modal iframe PDF)
    // ---------------------------------------------------------------
    public ?int $bitacoraViendoId = null;

    public function verBitacora(int $id): void
    {
        $this->bitacoraViendoId = $id;
    }

    public function cerrarBitacora(): void
    {
        $this->bitacoraViendoId = null;
    }

    public function getBitacoraViendo(): ?BitacoraReporte
    {
        return $this->bitacoraViendoId
            ? BitacoraReporte::find($this->bitacoraViendoId)
            : null;
    }

    // ---------------------------------------------------------------
    // Ver un registro inline (visor PDF dentro de Documentos)
    // ---------------------------------------------------------------
    public function verRegistroDoc(int $registroId): void
    {
        $registro = Registro::with('formato')->findOrFail($registroId);

        $data    = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
        $campos  = $data['campos']  ?? [];
        $valores = $data['valores'] ?? [];

        // Si el documento fue firmado en el portal, agregar la firma como campo sintético
        if ($registro->firma_jefe_data) {
            $fj = json_decode($registro->firma_jefe_data, true);
            if ($fj && !empty($fj['firma_svg'])) {
                $campoFirma = [
                    'id'    => '__firma_jefe__',
                    'page'  => (int) ($fj['page'] ?? 1),
                    'x'     => (float) ($fj['x'] ?? 0),
                    'y'     => (float) ($fj['y'] ?? 0),
                    'w'     => (float) ($fj['w'] ?? 18),
                    'h'     => (float) ($fj['h'] ?? 8),
                    'label' => 'Firma jefe',
                    'tipo'  => 'firma_jefe',
                ];
                $campos[] = $campoFirma;
                $valores['__firma_jefe__'] = $fj['firma_svg'];
            }
        }

        $this->registroViendoId = $registroId;
        $this->vistaDoc         = 'ver';

        $this->dispatch('fmt:ver-pdf',
            url:     route('formato.archivo', $registro->formato),
            campos:  $campos,
            valores: $valores ?: (object)[],
        );
    }

    public function getRegistroViendo(): ?Registro
    {
        return $this->registroViendoId
            ? Registro::with(['formato', 'usuario'])->find($this->registroViendoId)
            : null;
    }

    public function volverARegistros(): void
    {
        $this->vistaDoc         = 'lista';
        $this->registroViendoId = null;
        $this->tabActiva        = 'registros';
    }

    // ---------------------------------------------------------------
    // Modal: enviar registro a Jefe de Servicio
    // ---------------------------------------------------------------
    public ?int    $regEnviarId     = null;
    public ?int    $regEnviarJefeId = null;
    public ?string $regEnviarTipo   = null;
    public ?string $regEnviarError  = null;

    public function abrirModalEnviar(int $registroId): void
    {
        $this->regEnviarId     = $registroId;
        $this->regEnviarJefeId = null;
        $this->regEnviarTipo   = null;
        $this->regEnviarError  = null;
    }

    public function cerrarModalEnviar(): void
    {
        $this->regEnviarId     = null;
        $this->regEnviarJefeId = null;
        $this->regEnviarTipo   = null;
        $this->regEnviarError  = null;
    }

    public function enviarRegistroAJefe(): void
    {
        if (! $this->regEnviarJefeId) {
            $this->regEnviarError = 'Selecciona un Jefe de Servicio.';
            return;
        }
        if (! $this->regEnviarTipo) {
            $this->regEnviarError = 'Selecciona el tipo de documento.';
            return;
        }

        Registro::where('id', $this->regEnviarId)
            ->where('estado', 'pendiente')
            ->update([
                'estado'         => 'en_firma',
                'jefe_id'        => $this->regEnviarJefeId,
                'tipo_documento' => $this->regEnviarTipo,
            ]);

        $this->cerrarModalEnviar();
    }

    // ---------------------------------------------------------------
    // Filtro de vales — sub-pestaña por estado
    // ---------------------------------------------------------------
    public string $vSubTab = 'pendientes'; // pendientes | en_firma | culminados

    public function cambiarSubTabVales(string $sub): void
    {
        $this->vSubTab    = $sub;
        $this->vBusqueda  = '';
        $this->vFechaDesde = '';
        $this->vFechaHasta = '';
    }

    // ---------------------------------------------------------------
    // Concretar vale — modal
    // ---------------------------------------------------------------
    public function abrirConcretarVale(int $valeId): void
    {
        $vale = ValeInventario::find($valeId);
        if (! $vale) return;

        $this->redirect(
            \App\Filament\Resources\ValeInventarioResource::getUrl('concretar', ['record' => $vale])
        );
    }
}
