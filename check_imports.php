<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Models\FailedImportRow;

echo "=== Últimas Importaciones ===\n";
$imports = Import::latest()->take(3)->get();

foreach ($imports as $import) {
    echo "\nImport ID: {$import->id}\n";
    echo "Archivo: {$import->file_name}\n";
    echo "Importador: {$import->importer}\n";
    echo "Total filas: {$import->total_rows}\n";
    echo "Procesadas: {$import->processed_rows}\n";
    echo "Exitosas: {$import->successful_rows}\n";
    echo "Completado: " . ($import->completed_at ? 'Sí' : 'No') . "\n";
    
    // Ver errores
    $failedRows = FailedImportRow::where('import_id', $import->id)->take(3)->get();
    
    if ($failedRows->count() > 0) {
        echo "\n--- Errores encontrados ---\n";
        foreach ($failedRows as $failedRow) {
            echo "Fila {$failedRow->row_number}:\n";
            $data = json_decode($failedRow->data, true);
            $validationErrors = json_decode($failedRow->validation_error, true);
            
            if ($validationErrors) {
                echo "  Errores de validación: " . print_r($validationErrors, true) . "\n";
            }
            
            echo "  Datos: " . substr(json_encode($data), 0, 200) . "...\n";
        }
    }
    
    echo str_repeat("-", 60) . "\n";
}
