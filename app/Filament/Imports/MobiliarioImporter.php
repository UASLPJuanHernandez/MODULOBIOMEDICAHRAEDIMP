<?php

namespace App\Filament\Imports;

use App\Models\Mobiliario;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

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
                ->guess(['numero inventario', 'no. inventario', 'inventario']),
            
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
                ->guess(['numero serie', 'no. serie', 'serie']),
            
            ImportColumn::make('precio')
                ->label('Precio')
                ->requiredMapping()
                ->numeric(decimalPlaces: 2)
                ->rules(['required', 'numeric', 'min:0'])
                ->example('15000.00')
                ->castStateUsing(function (string $state): ?float {
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
                ->rules(['nullable', 'integer', 'exists:proveedor,id'])
                ->example('1')
                ->helperText('ID del proveedor (opcional)'),
            
            ImportColumn::make('metodo_adquisicion')
                ->label('Método de Adquisición')
                ->rules(['nullable', 'max:255'])
                ->example('Compra directa'),
            
            ImportColumn::make('tiene_folio')
                ->label('Tiene Folio')
                ->boolean()
                ->rules(['required', 'boolean'])
                ->example('1')
                ->guess(['tiene folio', 'folio']),
            
            ImportColumn::make('numero_folio')
                ->label('Número de Folio')
                ->rules(['nullable', 'max:255'])
                ->example('FOL-2024-001')
                ->guess(['numero folio', 'no. folio']),
            
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
                ->rules(['required', 'boolean'])
                ->example('0')
                ->guess(['tiene accesorios', 'accesorios']),
            
            ImportColumn::make('descripcion_accesorios')
                ->label('Descripción de Accesorios')
                ->rules(['nullable'])
                ->example('Mouse y teclado inalámbrico'),
            
            ImportColumn::make('dado_de_baja')
                ->label('Dado de Baja')
                ->boolean()
                ->rules(['nullable', 'boolean'])
                ->example('0'),
            
            ImportColumn::make('fecha_baja')
                ->label('Fecha de Baja')
                ->rules(['nullable', 'date'])
                ->example('2024-12-31'),
            
            ImportColumn::make('motivo_baja')
                ->label('Motivo de Baja')
                ->rules(['nullable'])
                ->example('Equipo obsoleto'),
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
        $body = 'La importación de mobiliario ha finalizado y ' . number_format($import->successful_rows) . ' ' . str('registro')->plural($import->successful_rows) . ' fueron importados exitosamente.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('registro')->plural($failedRowsCount) . ' fallaron al importar.';
        }

        return $body;
    }
}
