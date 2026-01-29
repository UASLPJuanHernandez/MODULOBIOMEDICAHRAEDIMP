<?php

namespace App\Filament\Imports;

use App\Models\Mobiliario;
use App\Services\AdminNotificationService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;

class MobiliarioImporter extends Importer
{
    protected static ?string $model = Mobiliario::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('numero_control')
                ->label('Número de Control')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('MOB-2024-001')
                ->guess(['numero control', 'no. control', 'control']),
            
            ImportColumn::make('numero_inventario')
                ->label('Número de Inventario')
                ->rules(['nullable', 'max:255'])
                ->example('INV-2024-001')
                ->guess(['numero inventario', 'no. inventario', 'inventario'])
                ->castStateUsing(function (?string $state): ?string {
                    return blank($state) ? null : $state;
                }),
            
            ImportColumn::make('clasificacion_bienes_id')
                ->label('ID Clasificación de Bienes')
                ->requiredMapping()
                ->numeric()
                ->relationship(resolveUsing: 'id')
                ->rules(['required', 'integer', 'exists:clasificacion_bienes,id'])
                ->example('1')
                ->helperText('ID de la clasificación del bien'),
            
            ImportColumn::make('caracteristicas')
                ->label('Características')
                ->requiredMapping()
                ->rules(['required'])
                ->example('Color negro, material plástico'),
            
            ImportColumn::make('descripcion')
                ->label('Descripción')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Silla ejecutiva ergonómica'),
            
            ImportColumn::make('marca')
                ->label('Marca')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('HP')
                ->ignoreBlankState(),
            
            ImportColumn::make('modelo')
                ->label('Modelo')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('EliteDesk 800')
                ->ignoreBlankState(),
            
            ImportColumn::make('numero_serie')
                ->label('Número de Serie')
                ->rules(['nullable', 'max:255'])
                ->example('SN123456789')
                ->guess(['numero serie', 'no. serie', 'serie'])
                ->castStateUsing(function (?string $state): ?string {
                    return blank($state) ? null : $state;
                }),
            
            ImportColumn::make('precio')
                ->label('Precio')
                ->requiredMapping()
                ->numeric(decimalPlaces: 2)
                ->rules(['required', 'numeric', 'min:0'])
                ->example('15000.00')
                ->castStateUsing(function (?string $state): ?float {
                    if (blank($state)) {
                        return null;
                    }
                    // Remover caracteres no numéricos excepto punto decimal
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),
            
            ImportColumn::make('tipo_mobiliario_id')
                ->label('ID Tipo de Mobiliario')
                ->requiredMapping()
                ->numeric()
                ->relationship(resolveUsing: 'id')
                ->rules(['required', 'integer', 'exists:tipo_mobiliario,id'])
                ->example('1')
                ->helperText('ID del tipo de mobiliario'),
            
            ImportColumn::make('localizacion_id')
                ->label('ID Localización')
                ->requiredMapping()
                ->numeric()
                ->relationship(resolveUsing: 'id')
                ->rules(['required', 'integer', 'exists:localizacion,id'])
                ->example('1')
                ->helperText('ID de la localización'),
            
            ImportColumn::make('proveedor_id')
                ->label('ID Proveedor')
                ->numeric()
                ->relationship(resolveUsing: 'id')
                ->rules(['nullable', 'integer'])
                ->example('1')
                ->helperText('ID del proveedor (opcional)')
                ->castStateUsing(function (?string $state): ?int {
                    if (blank($state) || $state === '0') {
                        return null;
                    }
                    return (int) $state;
                }),
            
            ImportColumn::make('metodo_adquisicion')
                ->label('Método de Adquisición')
                ->rules(['nullable', 'max:255'])
                ->example('Compra directa'),
            
            ImportColumn::make('donante')
                ->label('Donante')
                ->rules(['nullable', 'max:255'])
                ->example('Fundación XYZ')
                ->guess(['donante', 'donador', 'quien dona']),
            
            ImportColumn::make('tiene_folio')
                ->label('Tiene Folio')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('1')
                ->guess(['tiene folio', 'folio'])
                ->castStateUsing(function (?string $state): bool {
                    if (blank($state)) {
                        return false;
                    }
                    return in_array(strtolower($state), ['1', 'true', 'yes', 'sí', 'si', 'verdadero']);
                }),
            
            ImportColumn::make('numero_folio')
                ->label('Número de Folio')
                ->rules(['nullable', 'max:255'])
                ->example('FOL-2024-001')
                ->guess(['numero folio', 'no. folio'])
                ->castStateUsing(function (?string $state): ?string {
                    return blank($state) ? null : $state;
                }),
            
            ImportColumn::make('estado_mobiliario')
                ->label('Estado del Mobiliario')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'in:Nuevo,Usado,Baja,Restaurado'])
                ->example('Nuevo')
                ->guess(['estado', 'estado mobiliario'])
                ->helperText('Valores permitidos: Nuevo, Usado, Baja, Restaurado'),
            
            ImportColumn::make('tiene_accesorios')
                ->label('Tiene Accesorios')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('0')
                ->guess(['tiene accesorios', 'accesorios'])
                ->castStateUsing(function (?string $state): bool {
                    if (blank($state)) {
                        return false;
                    }
                    return in_array(strtolower($state), ['1', 'true', 'yes', 'sí', 'si', 'verdadero']);
                }),
            
            ImportColumn::make('descripcion_accesorios')
                ->label('Descripción de Accesorios')
                ->rules(['nullable'])
                ->example('Mouse y teclado inalámbrico')
                ->castStateUsing(function (?string $state): ?string {
                    return blank($state) ? null : $state;
                }),
            
            ImportColumn::make('dado_de_baja')
                ->label('Dado de Baja')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('0')
                ->castStateUsing(function (?string $state): bool {
                    if (blank($state)) {
                        return false;
                    }
                    return in_array(strtolower($state), ['1', 'true', 'yes', 'sí', 'si', 'verdadero']);
                }),
            
            ImportColumn::make('fecha_baja')
                ->label('Fecha de Baja')
                ->rules(['nullable'])
                ->example('2024-12-31')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    // Intentar parsear la fecha
                    try {
                        $fecha = \Carbon\Carbon::parse($state);
                        return $fecha->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Si falla el parseo, retornar null silenciosamente
                        return null;
                    }
                }),
            
            ImportColumn::make('motivo_baja')
                ->label('Motivo de Baja')
                ->rules(['nullable'])
                ->example('Equipo obsoleto')
                ->castStateUsing(function (?string $state): ?string {
                    return blank($state) ? null : $state;
                }),
        ];
    }

    public function resolveRecord(): ?Mobiliario
    {
        // Intentar actualizar si existe, crear si no
        // Buscar por numero_control para actualizar
        if ($this->options['updateExisting'] ?? false) {
            return Mobiliario::firstOrNew([
                'numero_control' => $this->data['numero_control'],
            ]);
        }

        // Solo crear nuevos registros
        return new Mobiliario();
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            \Filament\Forms\Components\Checkbox::make('updateExisting')
                ->label('Actualizar registros existentes')
                ->helperText('Si está marcado, actualizará los registros que coincidan por número de control. Si no, solo creará nuevos registros.')
                ->default(false),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $exitosos = $import->successful_rows;
        $fallidos = $import->getFailedRowsCount();
        
        $body = '📦 La importación de mobiliario ha finalizado.\n\n';
        $body .= '✅ Registros exitosos: ' . number_format($exitosos) . '\n';
        
        if ($fallidos > 0) {
            $body .= '❌ Registros fallidos: ' . number_format($fallidos) . '\n';
            $body .= '\n📄 Puedes descargar el archivo con los errores para corregirlos.';
        }
        
        // Enviar notificación al administrador
        if (Auth::check()) {
            AdminNotificationService::importacionCompletada(
                Auth::user(),
                'Mobiliario',
                $exitosos,
                $fallidos
            );
        }

        return $body;
    }
}
