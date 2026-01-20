<?php

namespace App\Console\Commands;

use App\Models\Mobiliario;
use App\Models\ClasificacionBien;
use App\Models\TipoMobiliario;
use App\Models\Localizacion;
use App\Models\Proveedor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportarMobiliarioLegacy extends Command
{
    protected $signature = 'mobiliario:importar-legacy {archivo} {--dry-run : Solo mostrar lo que se importaría sin guardar}';
    protected $description = 'Importar mobiliario desde CSV del sistema anterior';

    private $estadisticas = [
        'total' => 0,
        'exitosos' => 0,
        'fallidos' => 0,
        'errores' => [],
    ];

    public function handle()
    {
        $archivo = $this->argument('archivo');
        $dryRun = $this->option('dry-run');

        if (!file_exists($archivo)) {
            $this->error("El archivo '{$archivo}' no existe.");
            return 1;
        }

        $this->info("Iniciando importación desde: {$archivo}");
        if ($dryRun) {
            $this->warn("MODO DE PRUEBA: No se guardarán los datos");
        }
        
        $this->newLine();

        // Abrir archivo CSV
        $handle = fopen($archivo, 'r');
        if (!$handle) {
            $this->error("No se pudo abrir el archivo.");
            return 1;
        }

        // Detectar y convertir codificación
        $contenido = file_get_contents($archivo);
        $encoding = mb_detect_encoding($contenido, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $this->info("Convirtiendo de {$encoding} a UTF-8");
            $contenido = mb_convert_encoding($contenido, 'UTF-8', $encoding);
            $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
            file_put_contents($tempFile, $contenido);
            fclose($handle);
            $handle = fopen($tempFile, 'r');
        }

        // Leer encabezados (primera fila)
        $encabezados = fgetcsv($handle);
        if (!$encabezados) {
            $this->error("No se pudieron leer los encabezados del CSV.");
            fclose($handle);
            return 1;
        }

        $this->info("Columnas encontradas: " . count($encabezados));
        
        // Mapear columnas
        $columnMap = $this->mapearColumnas($encabezados);
        
        $this->newLine();
        $this->info("Procesando registros...");
        
        $barra = $this->output->createProgressBar();
        $barra->start();

        $fila = 0;
        while (($datos = fgetcsv($handle)) !== false) {
            $fila++;
            $this->estadisticas['total']++;
            
            try {
                $registro = $this->procesarFila($datos, $columnMap, $fila);
                
                if ($registro) {
                    if (!$dryRun) {
                        $registro->save();
                    }
                    $this->estadisticas['exitosos']++;
                    $barra->advance();
                } else {
                    // Fila vacía, no contar como error
                    $this->estadisticas['total']--;
                }
                
            } catch (\Exception $e) {
                $this->estadisticas['fallidos']++;
                $this->estadisticas['errores'][] = [
                    'fila' => $fila,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $barra->finish();
        fclose($handle);
        
        if (isset($tempFile)) {
            unlink($tempFile);
        }

        // Mostrar estadísticas
        $this->newLine(2);
        $this->info("═══════════════════════════════════════");
        $this->info("RESUMEN DE IMPORTACIÓN");
        $this->info("═══════════════════════════════════════");
        $this->info("Total de registros: {$this->estadisticas['total']}");
        $this->info("Exitosos: {$this->estadisticas['exitosos']}");
        
        if ($this->estadisticas['fallidos'] > 0) {
            $this->error("Fallidos: {$this->estadisticas['fallidos']}");
            $this->newLine();
            $this->error("Errores encontrados:");
            foreach ($this->estadisticas['errores'] as $error) {
                $this->error("  Fila {$error['fila']}: {$error['error']}");
            }
        }

        return 0;
    }

    private function mapearColumnas($encabezados)
    {
        $map = [];
        foreach ($encabezados as $index => $nombre) {
            $nombreLimpio = trim($nombre);
            $map[$nombreLimpio] = $index;
        }
        return $map;
    }

    private function procesarFila($datos, $columnMap, $numeroFila)
    {
        // Extraer datos según el mapeo
        $claveBien = $this->obtenerValor($datos, $columnMap, 'Clave del Bien');
        $nombreBien = $this->obtenerValor($datos, $columnMap, 'Nombre del Bien');
        $grupo = $this->obtenerValor($datos, $columnMap, 'Grupo');
        $subgrupo = $this->obtenerValor($datos, $columnMap, 'Subgrupo');
        $clase = $this->obtenerValor($datos, $columnMap, 'Clase');
        $marca = $this->obtenerValor($datos, $columnMap, 'Marca');
        $modelo = $this->obtenerValor($datos, $columnMap, 'Modelo');
        $color = $this->obtenerValor($datos, $columnMap, 'Color');
        $numeroSerie = $this->obtenerValor($datos, $columnMap, 'N. de Serie');
        $noFactura = $this->obtenerValor($datos, $columnMap, 'No Factura');
        $proveedor = $this->obtenerValor($datos, $columnMap, 'Proveedor');
        $metodoAdquisicion = $this->obtenerValor($datos, $columnMap, 'F. Adquisici½n') ?: $this->obtenerValor($datos, $columnMap, 'F. Adquisicion');
        $fechaBaja = $this->obtenerValor($datos, $columnMap, 'F. de  Baja') ?: $this->obtenerValor($datos, $columnMap, 'F. de Baja');
        $valor = $this->obtenerValor($datos, $columnMap, 'Valor');
        $direccion = $this->obtenerValor($datos, $columnMap, 'Direcci½n') ?: $this->obtenerValor($datos, $columnMap, 'Direccion');
        $caracteristicas = $this->obtenerValor($datos, $columnMap, 'Caracteristicas');

        // Validar datos mínimos requeridos (saltar filas vacías)
        if (empty($nombreBien)) {
            return null; // Saltar fila vacía
        }

        // Crear registro
        $mobiliario = new Mobiliario();

        // Generar número de control único
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(4));
        $mobiliario->numero_control = "IMP-{$timestamp}-{$numeroFila}-{$random}";
        
        // Datos básicos
        $mobiliario->numero_inventario = $claveBien;
        $mobiliario->descripcion = substr($nombreBien, 0, 255);
        
        // Características combinadas
        $caracteristicasArray = [];
        if ($color) $caracteristicasArray[] = "Color: {$color}";
        if ($grupo) $caracteristicasArray[] = "Grupo: {$grupo}";
        if ($subgrupo) $caracteristicasArray[] = "Subgrupo: {$subgrupo}";
        if ($clase) $caracteristicasArray[] = "Clase: {$clase}";
        if ($caracteristicas) $caracteristicasArray[] = $caracteristicas;
        
        $mobiliario->caracteristicas = !empty($caracteristicasArray) 
            ? implode(', ', $caracteristicasArray)
            : 'Importado del sistema anterior';
        
        $mobiliario->marca = $marca ?: 'Sin marca';
        $mobiliario->modelo = $modelo ?: 'Sin modelo';
        $mobiliario->numero_serie = $numeroSerie;
        
        // Precio
        $mobiliario->precio = $this->parsearPrecio($valor);
        
        // Método de adquisición
        $mobiliario->metodo_adquisicion = $metodoAdquisicion;
        
        // Folio
        $mobiliario->numero_folio = $noFactura;
        $mobiliario->tiene_folio = !empty($noFactura);
        
        // Estado
        if (!empty($fechaBaja)) {
            $mobiliario->estado_mobiliario = 'Baja';
            $mobiliario->dado_de_baja = true;
            $mobiliario->fecha_baja = $this->parsearFecha($fechaBaja);
            $mobiliario->motivo_baja = 'Importado del sistema anterior con fecha de baja';
        } else {
            $mobiliario->estado_mobiliario = 'Usado';
            $mobiliario->dado_de_baja = false;
        }
        
        $mobiliario->tiene_accesorios = false;
        
        // Relaciones
        $mobiliario->clasificacion_bienes_id = $this->resolverClasificacion($grupo, $subgrupo, $clase);
        $mobiliario->tipo_mobiliario_id = $this->resolverTipo();
        $mobiliario->localizacion_id = $this->resolverLocalizacion($direccion);
        
        if (!empty($proveedor)) {
            $mobiliario->proveedor_id = $this->resolverProveedor($proveedor);
        }
        
        // Valores por defecto
        $mobiliario->version = 1;
        $mobiliario->depreciacion_registrada = 0;
        $mobiliario->created_by = 1; // Usuario admin
        $mobiliario->updated_by = 1;

        return $mobiliario;
    }

    private function obtenerValor($datos, $columnMap, $nombreColumna)
    {
        if (!isset($columnMap[$nombreColumna])) {
            return null;
        }
        
        $index = $columnMap[$nombreColumna];
        $valor = isset($datos[$index]) ? trim($datos[$index]) : null;
        
        return empty($valor) ? null : $valor;
    }

    private function parsearPrecio($valor)
    {
        if (empty($valor)) {
            return 0;
        }
        
        // Remover caracteres no numéricos excepto punto y coma
        $valor = str_replace(',', '', $valor);
        $valor = preg_replace('/[^0-9.]/', '', $valor);
        
        return round(floatval($valor), 2);
    }

    private function parsearFecha($fecha)
    {
        if (empty($fecha)) {
            return null;
        }
        
        try {
            // Formato dd/mm/yyyy
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
                return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        } catch (\Exception $e) {
            // Ignorar errores de fecha
        }
        
        return null;
    }

    private function resolverClasificacion($grupo, $subgrupo, $clase)
    {
        static $cache = null;
        
        if ($cache === null) {
            $cache = ClasificacionBien::first();
            if (!$cache) {
                $cache = ClasificacionBien::create([
                    'codigo' => '0-0-0',
                    'tipo' => 'General',
                    'grupo' => 'Importado',
                    'clase' => 'Sin clasificar',
                    'cri' => 0,
                ]);
            }
        }
        
        return $cache->id;
    }

    private function resolverTipo()
    {
        static $cache = null;
        
        if ($cache === null) {
            $cache = TipoMobiliario::first();
            if (!$cache) {
                $cache = TipoMobiliario::create([
                    'tipo' => 'General',
                    'numero_secuencial' => 1,
                ]);
            }
        }
        
        return $cache->id;
    }

    private function resolverLocalizacion($direccion)
    {
        static $cache = [];
        
        if (!empty($direccion) && isset($cache[$direccion])) {
            return $cache[$direccion];
        }
        
        if (!empty($direccion)) {
            $localizacion = Localizacion::where('direccion', 'like', "%{$direccion}%")
                ->orWhere('division', 'like', "%{$direccion}%")
                ->first();
            
            if ($localizacion) {
                $cache[$direccion] = $localizacion->id;
                return $localizacion->id;
            }
        }
        
        // Usar primera localización o crear una por defecto
        $localizacion = Localizacion::first();
        if (!$localizacion) {
            $localizacion = Localizacion::create([
                'direccion' => 'Sin especificar',
                'division' => 'Importado',
                'sub_area' => 'Sin especificar',
                'ubicacion' => 'Sin especificar',
            ]);
        }
        
        return $localizacion->id;
    }

    private function resolverProveedor($nombre)
    {
        static $cache = [];
        
        if (isset($cache[$nombre])) {
            return $cache[$nombre];
        }
        
        $proveedor = Proveedor::where('nombre', $nombre)->first();
        
        if (!$proveedor) {
            $proveedor = Proveedor::create([
                'nombre' => substr($nombre, 0, 255),
                'rfc' => 'XAXX010101000',
                'telefono' => 'Sin especificar',
                'direccion' => 'Sin especificar',
            ]);
        }
        
        $cache[$nombre] = $proveedor->id;
        return $proveedor->id;
    }
}
