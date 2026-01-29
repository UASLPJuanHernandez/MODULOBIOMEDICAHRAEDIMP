<?php

namespace App\Filament\Imports;

use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use App\Models\Proveedor;
use App\Services\AdminNotificationService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MobiliarioLegacyImporter extends Importer
{
    protected static ?string $model = Mobiliario::class;

    // Caches estáticos para evitar múltiples consultas
    protected static array $localizacionCache = [];
    protected static ?int $defaultLocalizacionId = null;
    protected static array $clasificacionCache = [];
    protected static ?int $defaultClasificacionId = null;
    protected static ?int $defaultTipoMobiliarioId = null;
    protected static array $proveedorCache = [];

    public static function getColumns(): array
    {
        return [
            // Columna 1: Clave del Bien (número de inventario)
            ImportColumn::make('clave_bien')
                ->label('Clave del Bien (Núm. Inventario)')
                ->rules(['nullable', 'max:255'])
                ->example('42')
                ->guess(['Clave del Bien', 'clave_bien', 'clave', 'inventario']),
            
            // Columna 2: Nombre del Bien (descripción)
            ImportColumn::make('nombre_bien')
                ->label('Nombre del Bien (Descripción)')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('ESCRITORIO')
                ->guess(['Nombre del Bien', 'nombre_bien', 'nombre', 'descripcion']),
            
            // Columna 3: Grupo
            ImportColumn::make('grupo')
                ->label('Grupo')
                ->rules(['nullable'])
                ->example('5')
                ->guess(['Grupo', 'grupo']),
            
            // Columna 4: Subgrupo
            ImportColumn::make('subgrupo')
                ->label('Subgrupo')
                ->rules(['nullable'])
                ->example('1')
                ->guess(['Subgrupo', 'subgrupo']),
            
            // Columna 5: Clase
            ImportColumn::make('clase')
                ->label('Clase')
                ->rules(['nullable'])
                ->example('1')
                ->guess(['Clase', 'clase']),
            
            // Columna 6: Marca
            ImportColumn::make('marca')
                ->label('Marca')
                ->rules(['nullable', 'max:255'])
                ->example('HP')
                ->guess(['Marca', 'marca']),
            
            // Columna 7: Modelo
            ImportColumn::make('modelo')
                ->label('Modelo')
                ->rules(['nullable', 'max:255'])
                ->example('LASERJET')
                ->guess(['Modelo', 'modelo']),
            
            // Columna 8: Color
            ImportColumn::make('color')
                ->label('Color')
                ->rules(['nullable', 'max:255'])
                ->example('NEGRO')
                ->guess(['Color', 'color']),
            
            // Columna 9: N. de Serie
            ImportColumn::make('numero_serie')
                ->label('Número de Serie')
                ->rules(['nullable', 'max:255'])
                ->example('ABC123')
                ->guess(['N. de Serie', 'N de Serie', 'numero_serie', 'serie', 'No. de Serie']),
            
            // Columna 10: No Factura (número de folio)
            ImportColumn::make('no_factura')
                ->label('No. Factura (Núm. Folio)')
                ->rules(['nullable', 'max:255'])
                ->example('FOL-2024-001')
                ->guess(['No Factura', 'No. Factura', 'no_factura', 'factura', 'folio']),
            
            // Columna 11: Proveedor
            ImportColumn::make('proveedor')
                ->label('Proveedor')
                ->rules(['nullable', 'max:255'])
                ->example('PROVEEDOR XYZ')
                ->guess(['Proveedor', 'proveedor']),
            
            // Columna 14: F. Adquisición (método)
            ImportColumn::make('metodo_adquisicion')
                ->label('Forma de Adquisición')
                ->rules(['nullable', 'max:255'])
                ->example('COMPRA')
                ->guess(['F. Adquisicion', 'F. Adquisición', 'F Adquisicion', 'metodo_adquisicion', 'adquisicion']),
            
            // Columna 15: F. de Factura (fecha del vale)
            ImportColumn::make('fecha_factura')
                ->label('Fecha de Factura (Fecha Vale)')
                ->rules(['nullable'])
                ->example('31/12/2014')
                ->guess(['F. de Factura', 'F de Factura', 'fecha_factura']),
            
            // Columna 16: F. de Baja
            ImportColumn::make('fecha_baja')
                ->label('Fecha de Baja')
                ->rules(['nullable'])
                ->example('21/08/2018')
                ->guess(['F. de  Baja', 'F. de Baja', 'F de Baja', 'fecha_baja', 'baja']),
            
            // Columna 17: Valor (precio)
            ImportColumn::make('valor')
                ->label('Valor/Precio')
                ->requiredMapping()
                ->rules(['required'])
                ->example('1393.00')
                ->guess(['Valor', 'valor', 'precio'])
                ->castStateUsing(function (mixed $state): ?float {
                    if (blank($state)) {
                        return 0;
                    }
                    $cleanValue = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string)$state));
                    return round(floatval($cleanValue), 2);
                }),
            
            // Columna 18: F. Registro (fecha de alta)
            ImportColumn::make('fecha_registro')
                ->label('Fecha de Registro (Alta)')
                ->rules(['nullable'])
                ->example('31/12/2014')
                ->guess(['F. Registro', 'F Registro', 'fecha_registro', 'registro']),
            
            // Columna 26: Ubicación
            ImportColumn::make('ubicacion')
                ->label('Ubicación')
                ->rules(['nullable', 'max:255'])
                ->example('ALMACEN DE ACTIVO FIJO')
                ->guess(['Ubicacion', 'ubicación', 'ubicacion']),
            
            // Columna 37: Caracteristicas
            ImportColumn::make('caracteristicas')
                ->label('Características')
                ->rules(['nullable'])
                ->example('COLOR GRIS')
                ->guess(['Caracteristicas', 'caracteristicas', 'Características']),
            
            // Columna 38: Procedencia
            ImportColumn::make('procedencia')
                ->label('Procedencia')
                ->rules(['nullable', 'max:255'])
                ->example('HC')
                ->guess(['Procedencia', 'procedencia']),
            
            // Columna 39: Dirección
            ImportColumn::make('direccion')
                ->label('Dirección')
                ->rules(['nullable', 'max:255'])
                ->example('DIRECCION GENERAL')
                ->guess(['Direccion', 'Dirección', 'direccion']),
            
            // Columna 40: División
            ImportColumn::make('division')
                ->label('División')
                ->rules(['nullable', 'max:255'])
                ->example('DIVISION DE RECURSOS MATERIALES')
                ->guess(['Division', 'División', 'division']),
            
            // Columna 41: Departamento (sub_area)
            ImportColumn::make('departamento')
                ->label('Departamento (Sub Área)')
                ->rules(['nullable', 'max:255'])
                ->example('ACTIVO FIJO')
                ->guess(['Departamento', 'departamento', 'sub_area']),
            
            // Columna 42: Responsable
            ImportColumn::make('responsable')
                ->label('Responsable Actual')
                ->rules(['nullable', 'max:255'])
                ->example('L.D. ANA ROSA JUAREZ CONTRERAS')
                ->guess(['Responsable', 'responsable']),
            
            // Columna 43: Clave Emp. (matrícula)
            ImportColumn::make('clave_empleado')
                ->label('Matrícula del Responsable')
                ->rules(['nullable', 'max:255'])
                ->example('12345')
                ->guess(['Clave Emp.', 'Clave Emp', 'clave_empleado', 'matricula']),
            
            // Columna 44: Puesto
            ImportColumn::make('puesto')
                ->label('Puesto del Responsable')
                ->rules(['nullable', 'max:255'])
                ->example('Jefe de Departamento')
                ->guess(['Puesto', 'puesto']),
        ];
    }

    public function resolveRecord(): ?Mobiliario
    {
        return new Mobiliario();
    }

    protected function beforeFill(): void
    {
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $this->data[$key] = $this->limpiarTexto($value);
            }
        }
        
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $this->data['numero_control_generado'] = "IMP-{$timestamp}-{$random}";
    }

    protected function limpiarTexto(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }
        
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        return trim($texto);
    }

    protected function afterValidate(): void
    {
        $this->construirCaracteristicas();
    }

    protected function construirCaracteristicas(): void
    {
        $partes = [];
        
        if (!empty($this->data['caracteristicas'])) {
            $caracteristicas = preg_replace('/^[\+\-\*\s]+/', '', $this->data['caracteristicas']);
            $partes[] = trim($caracteristicas);
        }
        
        if (!empty($this->data['color'])) {
            $partes[] = "Color: {$this->data['color']}";
        }
        
        if (!empty($this->data['procedencia'])) {
            $partes[] = "Procedencia: {$this->data['procedencia']}";
        }
        
        $this->data['caracteristicas_combinadas'] = !empty($partes) 
            ? implode('. ', $partes)
            : 'Sin características especificadas';
    }

    protected function beforeSave(): void
    {
        try {
            Log::info('MobiliarioLegacyImporter::beforeSave', [
                'nombre_bien' => $this->data['nombre_bien'] ?? 'NO EXISTE',
                'clave_bien' => $this->data['clave_bien'] ?? 'NO EXISTE',
            ]);
            
            $this->record->numero_control = $this->data['numero_control_generado'];
            $this->record->numero_inventario = !empty($this->data['clave_bien']) 
                ? substr((string)$this->data['clave_bien'], 0, 255) 
                : null;
            $this->record->descripcion = substr($this->data['nombre_bien'] ?? 'Sin descripción', 0, 255);
            $this->record->caracteristicas = $this->data['caracteristicas_combinadas'] ?? 'Sin características';
            $this->record->marca = !empty($this->data['marca']) 
                ? substr($this->data['marca'], 0, 255) 
                : 'Sin marca';
            $this->record->modelo = !empty($this->data['modelo']) 
                ? substr($this->data['modelo'], 0, 255) 
                : 'Sin modelo';
            $this->record->numero_serie = !empty($this->data['numero_serie']) 
                ? substr((string)$this->data['numero_serie'], 0, 255) 
                : null;
            
            $this->record->precio = $this->parsearPrecio($this->data['valor'] ?? 0);
            $this->record->metodo_adquisicion = $this->data['metodo_adquisicion'] ?? null;
            
            $this->record->numero_folio = !empty($this->data['no_factura']) 
                ? substr((string)$this->data['no_factura'], 0, 255) 
                : null;
            $this->record->tiene_folio = !empty($this->data['no_factura']);
            
            $this->procesarEstadoMobiliario();
            
            $this->record->responsable_actual = !empty($this->data['responsable']) 
                ? substr($this->data['responsable'], 0, 255) 
                : null;
            $this->record->matricula_responsable = !empty($this->data['clave_empleado']) 
                ? substr((string)$this->data['clave_empleado'], 0, 255) 
                : null;
            $this->record->puesto_responsable = !empty($this->data['puesto']) 
                ? substr($this->data['puesto'], 0, 255) 
                : null;
            
            $this->record->clasificacion_bienes_id = $this->resolverClasificacionBien();
            $this->record->tipo_mobiliario_id = $this->resolverTipoMobiliario();
            $this->record->localizacion_id = $this->resolverLocalizacion();
            $this->record->proveedor_id = $this->resolverProveedor();
            
            $this->record->tiene_accesorios = false;
            $this->record->descripcion_accesorios = null;
            $this->record->version = 1;
            $this->record->depreciacion_registrada = 0;
            $this->record->created_by = auth()->id() ?? 1;
            $this->record->updated_by = auth()->id() ?? 1;
            
            if (!empty($this->data['fecha_registro'])) {
                $fechaRegistro = $this->parsearFecha($this->data['fecha_registro']);
                if ($fechaRegistro) {
                    $this->data['_fecha_registro_parseada'] = $fechaRegistro;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error en beforeSave de MobiliarioLegacyImporter: ' . $e->getMessage(), [
                'data' => $this->data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function afterSave(): void
    {
        if (!empty($this->data['_fecha_registro_parseada'])) {
            try {
                $this->record->timestamps = false;
                $this->record->created_at = $this->data['_fecha_registro_parseada'];
                $this->record->save();
                $this->record->timestamps = true;
            } catch (\Exception $e) {
                Log::warning('No se pudo actualizar fecha de registro: ' . $e->getMessage());
            }
        }
    }

    protected function parsearPrecio($valor): float
    {
        if (is_numeric($valor)) {
            return max(0, round(floatval($valor), 2));
        }
        
        $limpio = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string)$valor));
        return max(0, round(floatval($limpio), 2));
    }

    protected function procesarEstadoMobiliario(): void
    {
        try {
            $fechaBajaStr = $this->data['fecha_baja'] ?? null;
            
            if (!empty($fechaBajaStr) && trim((string)$fechaBajaStr) !== '') {
                $fechaBaja = $this->parsearFecha($fechaBajaStr);
                
                if ($fechaBaja) {
                    $this->record->estado_mobiliario = 'Baja';
                    $this->record->dado_de_baja = true;
                    $this->record->fecha_baja = $fechaBaja;
                    $this->record->motivo_baja = 'Importado del sistema anterior con fecha de baja';
                } else {
                    $this->setEstadoActivo();
                }
            } else {
                $this->setEstadoActivo();
            }
        } catch (\Exception $e) {
            Log::warning('Error procesando estado mobiliario: ' . $e->getMessage());
            $this->setEstadoActivo();
        }
    }

    protected function setEstadoActivo(): void
    {
        $this->record->estado_mobiliario = 'Usado';
        $this->record->dado_de_baja = false;
        $this->record->fecha_baja = null;
        $this->record->motivo_baja = null;
    }

    protected function parsearFecha(?string $fecha): ?string
    {
        if (empty($fecha) || trim($fecha) === '') {
            return null;
        }
        
        $fecha = trim($fecha);
        
        try {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
                $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $anio = $matches[3];
                
                if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
                    return "{$anio}-{$mes}-{$dia}";
                }
            }
            
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $matches)) {
                if (checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1])) {
                    return $fecha;
                }
            }
            
            try {
                $carbon = Carbon::parse($fecha);
                return $carbon->format('Y-m-d');
            } catch (\Exception $e) {
                // Silenciar
            }
        } catch (\Exception $e) {
            Log::warning("Error parseando fecha '{$fecha}': " . $e->getMessage());
        }
        
        return null;
    }

    protected function resolverClasificacionBien(): int
    {
        $grupo = !empty($this->data['grupo']) ? intval($this->data['grupo']) : null;
        $subgrupo = !empty($this->data['subgrupo']) ? intval($this->data['subgrupo']) : null;
        $clase = !empty($this->data['clase']) ? intval($this->data['clase']) : null;
        
        if ($grupo !== null && $subgrupo !== null && $clase !== null) {
            $cacheKey = "{$grupo}-{$subgrupo}-{$clase}";
            
            if (!isset(self::$clasificacionCache[$cacheKey])) {
                $clasificacion = ClasificacionBien::where('grupo', $grupo)
                    ->where('subgrupo', $subgrupo)
                    ->where('clase', $clase)
                    ->first();
                
                if ($clasificacion) {
                    self::$clasificacionCache[$cacheKey] = $clasificacion->id;
                }
            }
            
            if (isset(self::$clasificacionCache[$cacheKey])) {
                return self::$clasificacionCache[$cacheKey];
            }
        }
        
        if (self::$defaultClasificacionId === null) {
            $primera = ClasificacionBien::first();
            self::$defaultClasificacionId = $primera ? $primera->id : 1;
        }
        
        return self::$defaultClasificacionId;
    }

    protected function resolverTipoMobiliario(): int
    {
        if (self::$defaultTipoMobiliarioId === null) {
            $primero = TipoMobiliario::first();
            self::$defaultTipoMobiliarioId = $primero ? $primero->id : 1;
        }
        
        return self::$defaultTipoMobiliarioId;
    }

    protected function resolverLocalizacion(): int
    {
        $direccion = $this->normalizarTextoUbicacion($this->data['direccion'] ?? '');
        $division = $this->normalizarTextoUbicacion($this->data['division'] ?? '');
        $departamento = $this->normalizarTextoUbicacion($this->data['departamento'] ?? '');
        $ubicacion = $this->normalizarTextoUbicacion($this->data['ubicacion'] ?? '');
        
        $cacheKey = md5("{$direccion}|{$division}|{$departamento}|{$ubicacion}");
        
        if (isset(self::$localizacionCache[$cacheKey])) {
            return self::$localizacionCache[$cacheKey];
        }
        
        if (!empty($direccion) || !empty($division) || !empty($departamento)) {
            if (!empty($division) && !empty($departamento)) {
                $localizacion = Localizacion::where(function($q) use ($division, $departamento) {
                    $q->where('division', 'like', "%{$division}%")
                      ->where('sub_area', 'like', "%{$departamento}%");
                })->first();
                
                if ($localizacion) {
                    self::$localizacionCache[$cacheKey] = $localizacion->id;
                    return $localizacion->id;
                }
            }
            
            if (!empty($direccion)) {
                $localizacion = Localizacion::where('direccion', 'like', "%{$direccion}%")->first();
                
                if ($localizacion) {
                    self::$localizacionCache[$cacheKey] = $localizacion->id;
                    return $localizacion->id;
                }
            }
            
            try {
                $nuevaLocalizacion = Localizacion::create([
                    'direccion' => !empty($direccion) ? $direccion : 'Sin especificar',
                    'division' => !empty($division) ? $division : 'Sin especificar',
                    'sub_area' => !empty($departamento) ? $departamento : 'Sin especificar',
                    'ubicacion' => !empty($ubicacion) ? $ubicacion : 'Importado del sistema anterior',
                ]);
                
                self::$localizacionCache[$cacheKey] = $nuevaLocalizacion->id;
                return $nuevaLocalizacion->id;
            } catch (\Exception $e) {
                Log::warning('Error creando localización: ' . $e->getMessage());
            }
        }
        
        if (self::$defaultLocalizacionId === null) {
            $primera = Localizacion::first();
            if ($primera) {
                self::$defaultLocalizacionId = $primera->id;
            } else {
                try {
                    $localizacion = Localizacion::create([
                        'direccion' => 'Sin especificar',
                        'division' => 'Sin especificar',
                        'sub_area' => 'Importado',
                        'ubicacion' => 'Importado del sistema anterior',
                    ]);
                    self::$defaultLocalizacionId = $localizacion->id;
                } catch (\Exception $e) {
                    self::$defaultLocalizacionId = 1;
                }
            }
        }
        
        self::$localizacionCache[$cacheKey] = self::$defaultLocalizacionId;
        return self::$defaultLocalizacionId;
    }

    protected function normalizarTextoUbicacion(?string $texto): string
    {
        if (empty($texto)) {
            return '';
        }
        
        $texto = mb_strtoupper(trim($texto), 'UTF-8');
        $texto = str_replace(['Ñ', 'Ð'], 'N', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        return $texto;
    }

    protected function resolverProveedor(): ?int
    {
        $nombreProveedor = $this->data['proveedor'] ?? null;
        
        if (empty($nombreProveedor) || $nombreProveedor === '0' || $nombreProveedor === 0) {
            return null;
        }
        
        $nombreProveedor = trim((string)$nombreProveedor);
        
        if (empty($nombreProveedor)) {
            return null;
        }
        
        if (isset(self::$proveedorCache[$nombreProveedor])) {
            return self::$proveedorCache[$nombreProveedor];
        }
        
        try {
            $proveedor = Proveedor::where('nombre', $nombreProveedor)->first();
            
            if (!$proveedor) {
                $proveedor = Proveedor::create([
                    'nombre' => substr($nombreProveedor, 0, 255),
                    'rfc' => 'XAXX010101000',
                    'telefono' => 'Sin especificar',
                    'direccion' => 'Sin especificar',
                ]);
            }
            
            self::$proveedorCache[$nombreProveedor] = $proveedor->id;
            return $proveedor->id;
        } catch (\Exception $e) {
            Log::warning('Error resolviendo proveedor: ' . $e->getMessage());
            return null;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $exitosos = $import->successful_rows;
        $fallidos = $import->getFailedRowsCount();
        
        $body = "La importación de mobiliario ha finalizado.\n\n";
        $body .= "Registros exitosos: " . number_format($exitosos) . "\n";
        
        if ($fallidos > 0) {
            $body .= "Registros fallidos: " . number_format($fallidos) . "\n";
            $body .= "\nPuedes descargar el archivo con los errores para corregirlos.";
        }
        
        if (Auth::check()) {
            try {
                AdminNotificationService::importacionCompletada(
                    Auth::user(),
                    'Mobiliario (Sistema Anterior)',
                    $exitosos,
                    $fallidos
                );
            } catch (\Exception $e) {
                Log::warning('Error enviando notificación: ' . $e->getMessage());
            }
        }

        return $body;
    }

    public static function resetCaches(): void
    {
        self::$localizacionCache = [];
        self::$defaultLocalizacionId = null;
        self::$clasificacionCache = [];
        self::$defaultClasificacionId = null;
        self::$defaultTipoMobiliarioId = null;
        self::$proveedorCache = [];
    }
}
