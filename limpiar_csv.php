<?php

/**
 * Script para limpiar CSV del sistema anterior
 * Elimina columnas duplicadas y corrige problemas de codificación
 * 
 * Uso: php limpiar_csv.php archivo_original.csv archivo_limpio.csv
 */

if ($argc < 3) {
    echo "Uso: php limpiar_csv.php archivo_original.csv archivo_limpio.csv\n";
    exit(1);
}

$archivoOriginal = $argv[1];
$archivoLimpio = $argv[2];

if (!file_exists($archivoOriginal)) {
    echo "Error: El archivo '$archivoOriginal' no existe.\n";
    exit(1);
}

echo "Leyendo archivo: $archivoOriginal\n";

// Leer el archivo con la codificación correcta
$contenido = file_get_contents($archivoOriginal);

// Intentar detectar y convertir la codificación
$encoding = mb_detect_encoding($contenido, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
if ($encoding && $encoding !== 'UTF-8') {
    echo "Convirtiendo de $encoding a UTF-8\n";
    $contenido = mb_convert_encoding($contenido, 'UTF-8', $encoding);
}

// Guardar temporalmente para leer con fgetcsv
$tempFile = tempnam(sys_get_temp_dir(), 'csv_');
file_put_contents($tempFile, $contenido);

// Leer el CSV
$handle = fopen($tempFile, 'r');
if (!$handle) {
    echo "Error: No se pudo abrir el archivo.\n";
    exit(1);
}

$todasLasFilas = [];
$encabezados = null;

while (($fila = fgetcsv($handle, 0, ',')) !== false) {
    $todasLasFilas[] = $fila;
}
fclose($handle);
unlink($tempFile);

if (empty($todasLasFilas)) {
    echo "Error: El archivo está vacío.\n";
    exit(1);
}

// Primera fila son los encabezados
$encabezadosOriginales = $todasLasFilas[0];
$datosFilas = array_slice($todasLasFilas, 1);

echo "Encabezados originales encontrados: " . count($encabezadosOriginales) . "\n";
echo "Filas de datos: " . count($datosFilas) . "\n";

// Limpiar encabezados y eliminar duplicados
$encabezadosLimpios = [];
$indicesAMantener = [];
$encabezadosVistos = [];

foreach ($encabezadosOriginales as $index => $encabezado) {
    // Limpiar el encabezado
    $encabezadoLimpio = trim($encabezado);
    $encabezadoLimpio = preg_replace('/[^\x20-\x7E]/u', '', $encabezadoLimpio); // Eliminar caracteres raros
    $encabezadoLimpio = str_replace(['�', '½', '¿', '¡'], '', $encabezadoLimpio);
    
    // Si está vacío, asignar un nombre genérico
    if (empty($encabezadoLimpio)) {
        $encabezadoLimpio = "Columna_$index";
    }
    
    // Si ya existe este encabezado, agregar un sufijo
    if (in_array($encabezadoLimpio, $encabezadosVistos)) {
        $contador = 2;
        $nuevoNombre = $encabezadoLimpio . "_$contador";
        while (in_array($nuevoNombre, $encabezadosVistos)) {
            $contador++;
            $nuevoNombre = $encabezadoLimpio . "_$contador";
        }
        $encabezadoLimpio = $nuevoNombre;
        echo "Advertencia: Encabezado duplicado renombrado a: $encabezadoLimpio\n";
    }
    
    $encabezadosVistos[] = $encabezadoLimpio;
    $encabezadosLimpios[] = $encabezadoLimpio;
    $indicesAMantener[] = $index;
}

echo "Encabezados después de limpieza: " . count($encabezadosLimpios) . "\n";

// Crear el nuevo archivo CSV
$handleSalida = fopen($archivoLimpio, 'w');
if (!$handleSalida) {
    echo "Error: No se pudo crear el archivo de salida.\n";
    exit(1);
}

// Escribir encabezados limpios
fputcsv($handleSalida, $encabezadosLimpios);

// Escribir filas de datos (solo las columnas que mantuvimos)
foreach ($datosFilas as $fila) {
    $filaLimpia = [];
    foreach ($indicesAMantener as $index) {
        $filaLimpia[] = isset($fila[$index]) ? $fila[$index] : '';
    }
    fputcsv($handleSalida, $filaLimpia);
}

fclose($handleSalida);

echo "\n✓ Archivo limpio generado: $archivoLimpio\n";
echo "✓ Total de registros procesados: " . count($datosFilas) . "\n";
echo "\nAhora puedes importar el archivo '$archivoLimpio' en el sistema.\n";
