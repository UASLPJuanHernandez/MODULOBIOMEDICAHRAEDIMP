<?php

namespace App\Filament\Imports;

use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use App\Models\Proveedor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class MobiliarioLegacyImporter extends Importer
{
    protected static ?string $model = Mobiliario::class;

    public static function getColumns(): array
    {
        return [
            // Columna A: Clave del Bien (lo usaremos como numero_inventario)
            ImportColumn::make('clave_bien')
                ->label('Clave del Bien (Número Inventario Anterior)')
                ->rules(['nullable', 'max:255'])
                ->example('1'),
            
            // Columna B: Nombre del Bien (descripción)
            ImportColumn::make('nombre_bien')
                ->label('Nombre del Bien (Descripción)')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('ESCRITORIO'),
            
            // Columna C: Grupo
            ImportColumn::make('grupo')
                ->label('Grupo')
                ->rules(['nullable'])
                ->example('5'),
            
            // Columna D: Subgrupo
            ImportColumn::make('subgrupo')
                ->label('Subgrupo')
                ->rules(['nullable'])
                ->example('1'),
            
            // Columna E: Clase
            ImportColumn::make('clase')
                ->label('Clase')
                ->rules(['nullable'])
                ->example('1'),
            
            // Columna F: Marca
            ImportColumn::make('marca')
                ->label('Marca')
                ->rules(['nullable', 'max:255'])
                ->example('CARL ZEISS'),
            
            // Columna G: Modelo
            ImportColumn::make('modelo')
                ->label('Modelo')
                ->rules(['nullable', 'max:255'])
                ->example('MG'),
            
            // Columna H: Color
            ImportColumn::make('color')
                ->label('Color')
                ->rules(['nullable', 'max:255'])
                ->example('NEGRO'),
            
            // Columna I: N. de Serie
            ImportColumn::make('numero_serie')
                ->label('Número de Serie')
                ->rules(['nullable', 'max:255'])
                ->example('30577'),
            
            // Columna J: No Factura
            ImportColumn::make('no_factura')
                ->label('Número de Factura')
                ->rules(['nullable', 'max:255'])
                ->example('FOL-2024-001'),
            
            // Columna K: Proveedor
            ImportColumn::make('proveedor')
                ->label('Proveedor')
                ->rules(['nullable', 'max:255'])
                ->example('Proveedor SA'),
            
            // Columna N: F. Adquisición (método)
            ImportColumn::make('metodo_adquisicion')
                ->label('Forma de Adquisición')
                ->rules(['nullable', 'max:255'])
                ->example('COMPRA'),
            
            // Columna O: F. de Factura
            ImportColumn::make('fecha_factura')
                ->label('Fecha de Factura')
                ->rules(['nullable'])
                ->example('31/12/2014'),
            
            // Columna P: F. de Baja
            ImportColumn::make('fecha_baja')
                ->label('Fecha de Baja')
                ->rules(['nullable'])
                ->example('21/08/2018'),
            
            // Columna Q: Valor (precio)
            ImportColumn::make('valor')
                ->label('Valor/Precio')
                ->requiredMapping()
                ->rules(['required'])
                ->example('1393.00')
                ->castStateUsing(function (mixed $state): ?float {
                    if (blank($state)) {
                        return 0;
                    }
                    $state = preg_replace('/[^0-9.]/', '', (string)$state);
                    return round(floatval($state), 2);
                }),
            
            // Columna W: Dirección (localización)
            ImportColumn::make('direccion')
                ->label('Dirección/Ubicación')
                ->rules(['nullable', 'max:255'])
                ->example('DA'),
        ];
    }

    public function resolveRecord(): ?Mobiliario
    {
        // Solo crear nuevos registros, no actualizar
        return new Mobiliario();
    }

    protected function beforeFill(): void
    {
        // Generar número de control único
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $this->data['numero_control_generado'] = "IMP-{$timestamp}-{$random}";
    }

    protected function afterValidate(): void
    {
        // Combinar información para características
        $caracteristicas = [];
        
        if (!empty($this->data['color'])) {
            $caracteristicas[] = "Color: {$this->data['color']}";
        }
        
        if (!empty($this->data['grupo'])) {
            $caracteristicas[] = "Grupo: {$this->data['grupo']}";
        }
        
        if (!empty($this->data['subgrupo'])) {
            $caracteristicas[] = "Subgrupo: {$this->data['subgrupo']}";
        }
        
        if (!empty($this->data['clase'])) {
            $caracteristicas[] = "Clase: {$this->data['clase']}";
        }
        
        $this->data['caracteristicas_combinadas'] = !empty($caracteristicas) 
            ? implode(', ', $caracteristicas)
            : 'Sin características especificadas';
    }

    protected function beforeSave(): void
    {
        // Asignar valores al modelo
        $this->record->numero_control = $this->data['numero_control_generado'];
        $this->record->numero_inventario = $this->data['clave_bien'] ?? null;
        $this->record->descripcion = $this->data['nombre_bien'];
        $this->record->caracteristicas = $this->data['caracteristicas_combinadas'];
        $this->record->marca = $this->data['marca'] ?? 'Sin marca';
        $this->record->modelo = $this->data['modelo'] ?? 'Sin modelo';
        $this->record->numero_serie = $this->data['numero_serie'] ?? null;
        $this->record->precio = $this->data['valor'] ?? 0;
        $this->record->metodo_adquisicion = $this->data['metodo_adquisicion'] ?? null;
        $this->record->numero_folio = $this->data['no_factura'] ?? null;
        $this->record->tiene_folio = !empty($this->data['no_factura']);
        
        // Estado del mobiliario basado en fecha de baja
        if (!empty($this->data['fecha_baja'])) {
            $this->record->estado_mobiliario = 'Baja';
            $this->record->dado_de_baja = true;
            $this->record->fecha_baja = $this->parseFecha($this->data['fecha_baja']);
            $this->record->motivo_baja = 'Importado del sistema anterior con fecha de baja';
        } else {
            $this->record->estado_mobiliario = 'Usado';
            $this->record->dado_de_baja = false;
        }
        
        $this->record->tiene_accesorios = false;
        
        // Buscar o crear clasificación de bienes (usar ID 1 por defecto)
        $clasificacionId = $this->resolverClasificacionBien();
        $this->record->clasificacion_bienes_id = $clasificacionId;
        
        // Buscar o crear tipo de mobiliario (usar ID 1 por defecto)
        $tipoId = $this->resolverTipoMobiliario();
        $this->record->tipo_mobiliario_id = $tipoId;
        
        // Buscar o crear localización
        $localizacionId = $this->resolverLocalizacion();
        $this->record->localizacion_id = $localizacionId;
        
        // Buscar o crear proveedor si existe
        if (!empty($this->data['proveedor'])) {
            $proveedorId = $this->resolverProveedor();
            $this->record->proveedor_id = $proveedorId;
        }
        
        // Valores por defecto
        $this->record->version = 1;
        $this->record->depreciacion_registrada = 0;
        $this->record->created_by = auth()->id();
        $this->record->updated_by = auth()->id();
    }

    protected function resolverClasificacionBien(): int
    {
        // Intentar encontrar por los códigos de grupo/subgrupo/clase
        if (!empty($this->data['grupo']) && !empty($this->data['subgrupo']) && !empty($this->data['clase'])) {
            $codigo = sprintf('%d-%d-%d', $this->data['grupo'], $this->data['subgrupo'], $this->data['clase']);
            
            $clasificacion = ClasificacionBien::where('codigo', $codigo)->first();
            if ($clasificacion) {
                return $clasificacion->id;
            }
        }
        
        // Si no existe, usar la primera clasificación disponible o crear una por defecto
        $primeraClasificacion = ClasificacionBien::first();
        if ($primeraClasificacion) {
            return $primeraClasificacion->id;
        }
        
        // Si no hay ninguna, crear una por defecto
        $clasificacion = ClasificacionBien::create([
            'codigo' => '0-0-0',
            'tipo' => 'General',
            'grupo' => 'Importado',
            'clase' => 'Sin clasificar',
            'cri' => 0,
        ]);
        
        return $clasificacion->id;
    }

    protected function resolverTipoMobiliario(): int
    {
        // Usar el primer tipo disponible o crear uno por defecto
        $primerTipo = TipoMobiliario::first();
        if ($primerTipo) {
            return $primerTipo->id;
        }
        
        // Si no hay ninguno, crear uno por defecto
        $tipo = TipoMobiliario::create([
            'tipo' => 'General',
            'numero_secuencial' => 1,
        ]);
        
        return $tipo->id;
    }

    protected function resolverLocalizacion(): int
    {
        // Intentar buscar por dirección
        if (!empty($this->data['direccion'])) {
            $localizacion = Localizacion::where('siglas', $this->data['direccion'])
                ->orWhere('nombre', 'like', "%{$this->data['direccion']}%")
                ->first();
            
            if ($localizacion) {
                return $localizacion->id;
            }
        }
        
        // Usar la primera localización disponible o crear una por defecto
        $primeraLocalizacion = Localizacion::first();
        if ($primeraLocalizacion) {
            return $primeraLocalizacion->id;
        }
        
        // Si no hay ninguna, crear una por defecto
        $localizacion = Localizacion::create([
            'nombre' => 'Sin especificar',
            'siglas' => 'SE',
            'descripcion' => 'Localización importada del sistema anterior',
        ]);
        
        return $localizacion->id;
    }

    protected function resolverProveedor(): ?int
    {
        if (empty($this->data['proveedor'])) {
            return null;
        }
        
        // Buscar proveedor por nombre
        $proveedor = Proveedor::where('nombre', $this->data['proveedor'])->first();
        
        if (!$proveedor) {
            // Crear proveedor si no existe
            $proveedor = Proveedor::create([
                'nombre' => $this->data['proveedor'],
                'rfc' => 'XAXX010101000',
                'telefono' => 'Sin especificar',
                'direccion' => 'Sin especificar',
            ]);
        }
        
        return $proveedor->id;
    }

    protected function parseFecha(?string $fecha): ?string
    {
        if (empty($fecha)) {
            return null;
        }
        
        try {
            // Intentar parsear formato dd/mm/yyyy
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
                $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $anio = $matches[3];
                return "{$anio}-{$mes}-{$dia}";
            }
            
            // Si ya está en formato yyyy-mm-dd
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                return $fecha;
            }
        } catch (\Exception $e) {
            // Si falla el parseo, devolver null
        }
        
        return null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de mobiliario (formato anterior) ha finalizado y ' . number_format($import->successful_rows) . ' ' . str('registro')->plural($import->successful_rows) . ' fueron importados exitosamente.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('registro')->plural($failedRowsCount) . ' fallaron al importar.';
        }

        return $body;
    }
}
