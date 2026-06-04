<?php

namespace App\Filament\Pages;

use App\Models\Formato;
use App\Models\Ingeniero;
use App\Models\Registro;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class Formatos extends Page
{
    use WithFileUploads;

    protected static string $view          = 'filament.pages.formatos';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Formatos';
    protected static ?string $title           = 'Formatos';
    protected static ?int    $navigationSort  = -1;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return 'Formatos';
    }

    // ---------------------------------------------------------------
    // Estado de navegación: lista | crear | editar | historial | ver
    // ---------------------------------------------------------------
    public string $vista = 'lista';

    // --- Crear ---
    public string $nombre      = '';
    public $archivo            = null;
    public string $errorUpload = '';

    // --- Editar / Ver ---
    public ?int  $formatoId      = null;
    public ?int  $registroId     = null;
    public ?int  $borradorId     = null;
    public string $identificador = '';

    // --- Historial filtros ---
    public string $busquedaHistorial = '';
    public string $filtroUsuario     = '';
    public string $filtroFechaDesde  = '';
    public string $filtroFechaHasta  = '';

    // --- Modal mantenimientos recibidos (solo jefes de servicio) ---
    public bool   $modalMantenimientosAbierto = false;
    public ?int   $mantenimientoViendoId      = null;
    public string $mBusqueda                  = '';
    public string $mFiltroFormato             = '';
    public string $mFechaDesde                = '';
    public string $mFechaHasta                = '';

    // ---------------------------------------------------------------
    // Datos para la vista
    // ---------------------------------------------------------------

    public function getFormatos()
    {
        return Formato::withCount([
            'registros',
            'registros as borradores_count' => fn ($q) => $q->where('es_borrador', true),
        ])->latest()->get();
    }

    public function getFormatoActual(): ?Formato
    {
        return $this->formatoId ? Formato::find($this->formatoId) : null;
    }

    public function getBorradores()
    {
        if (!$this->formatoId) return collect();

        return Registro::where('formato_id', $this->formatoId)
            ->where('es_borrador', true)
            ->with('usuario')
            ->when($this->busquedaHistorial, fn ($q) =>
                $q->where('identificador', 'like', '%' . $this->busquedaHistorial . '%')
            )
            ->when($this->filtroUsuario, fn ($q) =>
                $q->where('usuario_id', $this->filtroUsuario)
            )
            ->when($this->filtroFechaDesde, fn ($q) =>
                $q->whereDate('created_at', '>=', $this->filtroFechaDesde)
            )
            ->when($this->filtroFechaHasta, fn ($q) =>
                $q->whereDate('created_at', '<=', $this->filtroFechaHasta)
            )
            ->latest()
            ->get();
    }

    public function getIngenieros()
    {
        return Ingeniero::activos()->orderBy('nombre')->get();
    }

    public function getRegistroActual(): ?Registro
    {
        return $this->registroId ? Registro::with('usuario')->find($this->registroId) : null;
    }

    // ---------------------------------------------------------------
    // Navegación
    // ---------------------------------------------------------------

    public function irCrear(): void
    {
        $this->reset(['nombre', 'archivo', 'errorUpload']);
        $this->vista = 'crear';
    }

    public function volverLista(): void
    {
        $this->reset(['formatoId', 'registroId', 'borradorId', 'identificador', 'errorUpload']);
        $this->vista = 'lista';
    }

    private function getIngenierosFirmas(): array
    {
        return Ingeniero::whereNotNull('firma_svg')
            ->where('firma_svg', '!=', '')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'cargo', 'firma_svg'])
            ->map(fn($i) => [
                'id'     => $i->id,
                'nombre' => $i->nombre,
                'cargo'  => $i->cargo,
                'firma'  => $i->firma_svg,
            ])
            ->values()
            ->toArray();
    }

    private function getJefasFirmas(): array
    {
        return \App\Models\PersonalReportante::where('es_jefe_servicio', true)
            ->where('estado', 'aprobado')
            ->whereNotNull('firma')
            ->where('firma', '!=', '')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'area_jefe_servicio', 'firma'])
            ->map(fn($j) => [
                'id'     => $j->id,
                'nombre' => $j->nombre,
                'cargo'  => $j->area_jefe_servicio,
                'firma'  => $j->firma,
            ])
            ->values()
            ->toArray();
    }

    public function irEditar(int $formatoId): void
    {
        $formato = Formato::findOrFail($formatoId);
        $this->formatoId     = $formatoId;
        $this->borradorId    = null;
        $this->identificador = '';
        $this->vista         = 'editar';

        $this->dispatch('fmt:editar-pdf',
            url:        route('formato.archivo', $formato),
            formatoId:  $formatoId,
            campos:     $formato->campos_json ?? [],
            valores:    (object)[],
            ingenieros: $this->getIngenierosFirmas(),
            jefas:      $this->getJefasFirmas(),
        );
    }

    public function continuarBorrador(int $registroId): void
    {
        $registro = Registro::with('formato')->findOrFail($registroId);

        $this->formatoId     = $registro->formato_id;
        $this->borradorId    = $registroId;
        $this->identificador = $registro->identificador ?? '';
        $this->vista         = 'editar';

        $data    = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
        $campos  = $data['campos']  ?? [];
        $valores = $data['valores'] ?? [];

        $this->dispatch('fmt:editar-pdf',
            url:        route('formato.archivo', $registro->formato),
            formatoId:  $this->formatoId,
            campos:     $campos,
            valores:    $valores ?: (object)[],
            ingenieros: $this->getIngenierosFirmas(),
            jefas:      $this->getJefasFirmas(),
        );
    }

    public function irBorradores(int $formatoId): void
    {
        $this->formatoId         = $formatoId;
        $this->busquedaHistorial = '';
        $this->filtroUsuario     = '';
        $this->filtroFechaDesde  = '';
        $this->filtroFechaHasta  = '';
        $this->vista             = 'borradores';
    }

    public function verRegistro(int $registroId): void
    {
        $this->registroId = $registroId;
        $registro = Registro::with('formato')->findOrFail($registroId);

        if (!$this->formatoId) {
            $this->formatoId = $registro->formato_id;
        }

        $data    = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
        $campos  = $data['campos']  ?? [];
        $valores = $data['valores'] ?? [];

        if ($registro->firma_jefe_data) {
            $fj = json_decode($registro->firma_jefe_data, true);
            if ($fj && !empty($fj['firma_svg'])) {
                $campos[] = [
                    'id'    => '__firma_jefe__',
                    'page'  => (int) ($fj['page'] ?? 1),
                    'x'     => (float) ($fj['x'] ?? 0),
                    'y'     => (float) ($fj['y'] ?? 0),
                    'w'     => (float) ($fj['w'] ?? 18),
                    'h'     => (float) ($fj['h'] ?? 8),
                    'label' => 'Firma jefe',
                    'tipo'  => 'firma_jefe',
                ];
                $valores['__firma_jefe__'] = $fj['firma_svg'];
            }
        }

        $this->dispatch('fmt:ver-pdf',
            url:     route('formato.archivo', $registro->formato),
            campos:  $campos,
            valores: $valores,
        );

        $this->vista = 'ver';
    }

    // ---------------------------------------------------------------
    // Subir archivo (solo PDF)
    // ---------------------------------------------------------------

    public function subirArchivo(): void
    {
        $this->validate([
            'nombre'  => 'required|string|max:255',
            'archivo' => 'required|file|max:20480',
        ], [
            'nombre.required'  => 'Escribe un nombre para el formato.',
            'archivo.required' => 'Selecciona un archivo PDF.',
        ]);

        $extension = strtolower($this->archivo->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            $this->errorUpload = 'Solo se aceptan archivos PDF.';
            return;
        }

        try {
            $originalName = $this->archivo->getClientOriginalName();

            $storedPath = $this->archivo->storeAs(
                'formatos',
                uniqid() . '_' . preg_replace('/[^a-z0-9._-]/i', '_', $originalName),
                'local'
            );

            $formato = Formato::create([
                'nombre'           => $this->nombre,
                'archivo_original' => $originalName,
                'archivo_path'     => $storedPath,
                'contenido_texto'  => null,
            ]);

            Notification::make()->title('Formato subido correctamente.')->success()->send();

            $this->irEditar($formato->id);

        } catch (\Throwable $e) {
            $this->errorUpload = 'Error al procesar el archivo: ' . $e->getMessage();
        }
    }

    // ---------------------------------------------------------------
    // PDF overlay — guardar plantilla de campos
    // ---------------------------------------------------------------

    public function guardarCamposPdf(string $camposJson): void
    {
        $formato = Formato::findOrFail($this->formatoId);
        $campos  = json_decode($camposJson, true) ?? [];
        $formato->update(['campos_json' => $campos]);
        Notification::make()->title('Plantilla guardada.')->success()->send();
    }

    // ---------------------------------------------------------------
    // PDF overlay — guardar registro con posiciones + valores
    // ---------------------------------------------------------------

    public function guardarRegistroPdf(string $camposJson, string $valoresJson): void
    {
        $campos  = json_decode($camposJson,  true) ?? [];
        $valores = json_decode($valoresJson, true) ?? [];

        if (empty($campos)) {
            Notification::make()->title('Agrega al menos un campo antes de guardar.')->warning()->send();
            return;
        }

        $contenido = json_encode([
            'tipo'    => 'pdf',
            'campos'  => $campos,
            'valores' => $valores,
        ]);

        if ($this->borradorId) {
            // Convertir borrador existente en registro final
            $registro = Registro::findOrFail($this->borradorId);
            $registro->update([
                'identificador'     => trim($this->identificador) ?: null,
                'contenido_editado' => $contenido,
                'es_borrador'       => false,
            ]);
            $this->borradorId = null;
        } else {
            $registro = Registro::create([
                'formato_id'        => $this->formatoId,
                'usuario_id'        => Auth::id(),
                'identificador'     => trim($this->identificador) ?: null,
                'contenido_editado' => $contenido,
                'es_borrador'       => false,
            ]);
        }

        Notification::make()->title('Registro guardado.')->success()->send();

        $this->registroId = $registro->id;

        $this->dispatch('fmt:ver-pdf',
            url:    route('formato.archivo', Formato::find($this->formatoId)),
            campos: $campos,
            valores: $valores,
        );

        $this->vista = 'ver';
    }

    // ---------------------------------------------------------------
    // PDF overlay — guardar borrador
    // ---------------------------------------------------------------

    public function guardarBorradorPdf(string $camposJson, string $valoresJson): void
    {
        $campos  = json_decode($camposJson,  true) ?? [];
        $valores = json_decode($valoresJson, true) ?? [];

        if (empty($campos)) {
            Notification::make()->title('Agrega al menos un campo antes de guardar el borrador.')->warning()->send();
            return;
        }

        $contenido = json_encode([
            'tipo'    => 'pdf',
            'campos'  => $campos,
            'valores' => $valores,
        ]);

        if ($this->borradorId) {
            // Actualizar borrador existente
            Registro::findOrFail($this->borradorId)->update([
                'identificador'     => trim($this->identificador) ?: null,
                'contenido_editado' => $contenido,
            ]);
        } else {
            // Crear nuevo borrador
            $borrador = Registro::create([
                'formato_id'        => $this->formatoId,
                'usuario_id'        => Auth::id(),
                'identificador'     => trim($this->identificador) ?: null,
                'contenido_editado' => $contenido,
                'es_borrador'       => true,
            ]);
            $this->borradorId = $borrador->id;
        }

        Notification::make()->title('Borrador guardado. Puedes continuar más tarde.')->success()->send();
    }

    // ---------------------------------------------------------------
    // Modal mantenimientos recibidos (jefes de servicio)
    // ---------------------------------------------------------------

    public function abrirModalMantenimientos(): void
    {
        $this->modalMantenimientosAbierto = true;
        $this->mantenimientoViendoId      = null;
        $this->mBusqueda                  = '';
        $this->mFiltroFormato             = '';
        $this->mFechaDesde                = '';
        $this->mFechaHasta                = '';
    }

    public function cerrarModalMantenimientos(): void
    {
        $this->modalMantenimientosAbierto = false;
        $this->mantenimientoViendoId      = null;
    }

    public function getMantenimientosPendientes()
    {
        return Registro::where('es_borrador', false)
            ->where('estado', 'pendiente')
            ->with(['formato:id,nombre', 'usuario:id,name'])
            ->when($this->mBusqueda, fn ($q) =>
                $q->where('identificador', 'like', '%' . $this->mBusqueda . '%')
            )
            ->when($this->mFiltroFormato, fn ($q) =>
                $q->where('formato_id', $this->mFiltroFormato)
            )
            ->when($this->mFechaDesde, fn ($q) =>
                $q->whereDate('created_at', '>=', $this->mFechaDesde)
            )
            ->when($this->mFechaHasta, fn ($q) =>
                $q->whereDate('created_at', '<=', $this->mFechaHasta)
            )
            ->latest()
            ->get();
    }

    public function verMantenimiento(int $registroId): void
    {
        $this->mantenimientoViendoId = $registroId;
        $registro = Registro::with('formato')->findOrFail($registroId);

        $data    = json_decode($registro->contenido_editado ?? '{}', true) ?? [];
        $campos  = $data['campos']  ?? [];
        $valores = $data['valores'] ?? [];

        if ($registro->firma_jefe_data) {
            $fj = json_decode($registro->firma_jefe_data, true);
            if ($fj && !empty($fj['firma_svg'])) {
                $campos[] = [
                    'id'    => '__firma_jefe__',
                    'page'  => (int) ($fj['page'] ?? 1),
                    'x'     => (float) ($fj['x'] ?? 0),
                    'y'     => (float) ($fj['y'] ?? 0),
                    'w'     => (float) ($fj['w'] ?? 18),
                    'h'     => (float) ($fj['h'] ?? 8),
                    'label' => 'Firma jefe',
                    'tipo'  => 'firma_jefe',
                ];
                $valores['__firma_jefe__'] = $fj['firma_svg'];
            }
        }

        $this->dispatch('fmt:ver-mantenimiento',
            url:     route('formato.archivo', $registro->formato),
            campos:  $campos,
            valores: $valores ?: (object)[],
        );
    }

    public function firmarMantenimiento(int $registroId): void
    {
        $registro = Registro::findOrFail($registroId);

        if ($registro->estado !== 'pendiente') {
            Notification::make()->title('Este mantenimiento ya fue procesado.')->warning()->send();
            return;
        }

        $registro->update([
            'estado'        => 'en_curso',
            'firmado_por_id' => Auth::id(),
            'firmado_at'    => now(),
        ]);

        $this->mantenimientoViendoId = null;

        Notification::make()
            ->title('Mantenimiento firmado y enviado correctamente.')
            ->success()
            ->send();
    }

    // ---------------------------------------------------------------
    // Renombrar formato
    // ---------------------------------------------------------------

    public function renombrarFormato(int $formatoId, string $nombre): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') return;

        Formato::findOrFail($formatoId)->update(['nombre' => $nombre]);
        Notification::make()->title('Nombre actualizado.')->success()->send();
    }

    // ---------------------------------------------------------------
    // Eliminar formato
    // ---------------------------------------------------------------

    public function eliminarFormato(int $formatoId): void
    {
        $formato = Formato::findOrFail($formatoId);
        if ($formato->archivo_path) {
            Storage::disk('local')->delete($formato->archivo_path);
        }
        $formato->delete();
        Notification::make()->title('Formato eliminado.')->success()->send();
    }
}
