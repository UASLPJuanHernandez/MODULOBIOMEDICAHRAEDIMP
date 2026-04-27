<?php

namespace App\Services;

use App\Models\InventarioEquipo;
use App\Models\ValeInventario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class ValeEntregaService
{
    // ─────────────────────────────────────────────
    //  API pública
    // ─────────────────────────────────────────────

    /**
     * Genera el vale de ENTREGA, registra en BD y devuelve la ruta temporal.
     */
    public function generarEntrega(InventarioEquipo $equipo): string
    {
        $this->registrarVale('entrega', $equipo);
        return $this->buildDocx(
            storage_path('app/templates/vale_entrega.docx'),
            fn (string $xml) => $this->rellenarEntrega($xml, $equipo)
        );
    }

    /**
     * Genera el vale de RETIRO, registra en BD y devuelve la ruta temporal.
     */
    public function generarRetiro(InventarioEquipo $equipo): string
    {
        $this->registrarVale('retiro', $equipo);
        return $this->buildDocx(
            storage_path('app/templates/vale_retiro.docx'),
            fn (string $xml) => $this->rellenarRetiro($xml, $equipo)
        );
    }

    /**
     * Registra el vale de retiro en BD y devuelve el modelo creado.
     * No genera el archivo físico; se descarga después desde la pestaña Vales.
     */
    public function registrarRetiro(InventarioEquipo $equipo): ValeInventario
    {
        return $this->registrarVale('retiro', $equipo);
    }

    /**
     * Alias sin descarga para la bulk action.
     */
    public function generarRetiroSinDescarga(InventarioEquipo $equipo): void
    {
        $this->registrarVale('retiro', $equipo);
    }

    /**
     * Re-genera un vale ya registrado (para descarga posterior desde el historial).
     */
    public function regenerar(ValeInventario $vale): string
    {
        $data = (object) [
            'area'              => $vale->area,
            'unidad_medica'     => $vale->unidad_medica,
            'equipo'            => $vale->equipo_nombre,
            'numero_inventario' => $vale->numero_inventario,
            'marca'             => $vale->marca,
            'modelo'            => $vale->modelo,
            'numero_serie'      => $vale->numero_serie,
        ];

        $template = $vale->tipo === 'retiro'
            ? storage_path('app/templates/vale_retiro.docx')
            : storage_path('app/templates/vale_entrega.docx');

        $filler = $vale->tipo === 'retiro'
            ? fn (string $xml) => $this->rellenarRetiro($xml, $data)
            : fn (string $xml) => $this->rellenarEntrega($xml, $data);

        return $this->buildDocx($template, $filler);
    }

    // ─────────────────────────────────────────────
    //  Registro en BD
    // ─────────────────────────────────────────────

    protected function registrarVale(string $tipo, InventarioEquipo $equipo): ValeInventario
    {
        return ValeInventario::create([
            'tipo'                 => $tipo,
            'inventario_equipo_id' => $equipo->id,
            'numero_inventario'    => $equipo->numero_inventario,
            'equipo_nombre'        => $equipo->equipo,
            'area'                 => $equipo->area,
            'unidad_medica'        => $equipo->unidad_medica,
            'marca'                => $equipo->marca,
            'modelo'               => $equipo->modelo,
            'numero_serie'         => $equipo->numero_serie,
            'usuario_id'           => Auth::id(),
            'usuario_nombre'       => Auth::user()?->name ?? 'Sistema',
        ]);
    }

    // ─────────────────────────────────────────────
    //  Generación DOCX
    // ─────────────────────────────────────────────

    protected function buildDocx(string $templatePath, callable $rellenar): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'vale_') . '.docx';
        copy($templatePath, $tmpPath);

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('No se pudo abrir el archivo DOCX temporal.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $xml = $rellenar($xml);

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return $tmpPath;
    }

    // ─────────────────────────────────────────────
    //  Relleno VALE DE ENTREGA
    //  Campos en orden de aparición:
    //   1. FECHA  2. ÁREA  3. DESCRIPCIÓN 1
    //   4. No INV 1  5. MARCA 1  6. MODELO 1  7. SERIE 1
    // ─────────────────────────────────────────────

    protected function rellenarEntrega(string $xml, object $equipo): string
    {
        $reemplazos = [
            Carbon::now()->format('d/m/Y'),
            $equipo->area              ?? '',
            $equipo->equipo            ?? '',
            $equipo->numero_inventario ?? '',
            $equipo->marca             ?? '',
            $equipo->modelo            ?? '',
            $equipo->numero_serie      ?? '',
        ];

        $xml = $this->reemplazarSecuencial($xml, $reemplazos);
        $xml = $this->eliminarItemsVaciosEntrega($xml);
        return $xml;
    }

    protected function eliminarItemsVaciosEntrega(string $xml): string
    {
        $marker  = '|||P|||';
        $xmlMark = preg_replace('/(<w:p[ >])/U', $marker . '$1', $xml);
        $partes  = explode($marker, $xmlMark);

        $eliminar = [];
        foreach ($partes as $i => $p) {
            preg_match_all('/<w:t[^>]*>([^<]*)<\/w:t>/s', $p, $m);
            $texto = implode('', $m[1]);
            foreach (['2-. ', '3-. ', '4-. ', '5-. '] as $mark) {
                if (str_contains($texto, $mark)) {
                    $eliminar[] = $i;
                    $eliminar[] = $i + 1;
                    $eliminar[] = $i + 2;
                    break;
                }
            }
        }

        return implode('', array_filter(
            $partes,
            fn ($i) => !in_array($i, $eliminar),
            ARRAY_FILTER_USE_KEY
        ));
    }

    // ─────────────────────────────────────────────
    //  Relleno VALE DE RETIRO
    //  Campos en orden de aparición:
    //   1. Fecha
    //   2. Unidad o Departamento
    //   3. Descripción del Bien
    //   4. Marca
    //   5. Modelo
    //   6. No. Inventario
    //   7. No. Mat  (se deja vacío)
    //   8. Cantidad (se pone "1")
    //   9. Recibe Nombre (se deja vacío)
    //  10. Observaciones (se deja vacío)
    // ─────────────────────────────────────────────

    protected function rellenarRetiro(string $xml, object $equipo): string
    {
        $reemplazos = [
            Carbon::now()->format('d/m/Y'),         // Fecha
            $equipo->unidad_medica ?? $equipo->area ?? '', // Unidad o Departamento
            $equipo->equipo            ?? '',        // Descripción del Bien
            $equipo->marca             ?? '',        // Marca
            $equipo->modelo            ?? '',        // Modelo
            $equipo->numero_inventario ?? '',        // No. Inventario
            '',                                      // No. Mat (manual)
            '1',                                     // Cantidad de Bienes
        ];

        return $this->reemplazarSecuencial($xml, $reemplazos);
    }

    // ─────────────────────────────────────────────
    //  Utilidades comunes
    // ─────────────────────────────────────────────

    /**
     * Reemplaza los primeros N campos de guiones bajos en orden de aparición.
     */
    protected function reemplazarSecuencial(string $xml, array $reemplazos): string
    {
        $idx = 0;
        return preg_replace_callback(
            '/<w:t(?:\s[^>]*)?>(\s*_+\s*)<\/w:t>/U',
            function (array $m) use (&$idx, $reemplazos) {
                if ($idx < count($reemplazos)) {
                    $valor = htmlspecialchars($reemplazos[$idx++], ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    return '<w:t xml:space="preserve"> ' . $valor . '</w:t>';
                }
                return $m[0];
            },
            $xml
        );
    }
}
