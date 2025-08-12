<?php

namespace App\Exports;

use App\Models\Mobiliario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class EquipoBajaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $selectedFields;
    
    public function __construct(array $selectedFields)
    {
        $this->selectedFields = $selectedFields;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try {
            return Mobiliario::with(['localizacion', 'movimientos'])
                ->dadosDeBaja()
                ->orderBy('fecha_baja', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error en EquipoBajaExport::collection: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Define los encabezados según los campos seleccionados
     */
    public function headings(): array
    {
        $headings = [];
        
        if (in_array('descripcion', $this->selectedFields)) {
            $headings[] = 'Descripción';
        }
        if (in_array('numero_inventario', $this->selectedFields)) {
            $headings[] = 'Número de Inventario';
        }
        if (in_array('marca', $this->selectedFields)) {
            $headings[] = 'Marca';
        }
        if (in_array('modelo', $this->selectedFields)) {
            $headings[] = 'Modelo';
        }
        if (in_array('numero_serie', $this->selectedFields)) {
            $headings[] = 'Número de Serie';
        }
        if (in_array('precio', $this->selectedFields)) {
            $headings[] = 'Precio Original (MXN)';
        }
        if (in_array('ubicacion', $this->selectedFields)) {
            $headings[] = 'Última Ubicación';
        }
        if (in_array('fecha_baja', $this->selectedFields)) {
            $headings[] = 'Fecha de Baja';
        }
        if (in_array('motivo_baja', $this->selectedFields)) {
            $headings[] = 'Motivo de Baja';
        }
        if (in_array('estado_mobiliario', $this->selectedFields)) {
            $headings[] = 'Estado del Mobiliario';
        }
        if (in_array('metodo_adquisicion', $this->selectedFields)) {
            $headings[] = 'Método de Adquisición';
        }
        
        return $headings;
    }

    /**
     * Mapea los datos según los campos seleccionados
     */
    public function map($mobiliario): array
    {
        $row = [];
        
        try {
            if (in_array('descripcion', $this->selectedFields)) {
                $row[] = $mobiliario->descripcion ?? 'N/A';
            }
            if (in_array('numero_inventario', $this->selectedFields)) {
                $row[] = $mobiliario->numero_control ?? 'N/A';
            }
            if (in_array('marca', $this->selectedFields)) {
                $row[] = $mobiliario->marca ?? 'N/A';
            }
            if (in_array('modelo', $this->selectedFields)) {
                $row[] = $mobiliario->modelo ?? 'N/A';
            }
            if (in_array('numero_serie', $this->selectedFields)) {
                $row[] = $mobiliario->numero_serie ?? 'N/A';
            }
            if (in_array('precio', $this->selectedFields)) {
                $precio = $mobiliario->precio ?? 0;
                $row[] = '$' . number_format($precio, 2) . ' MXN';
            }
            if (in_array('ubicacion', $this->selectedFields)) {
                $ubicacion = 'Sin ubicación';
                try {
                    if ($mobiliario->relationLoaded('localizacion') && $mobiliario->localizacion) {
                        $ubicacion = $mobiliario->localizacion->division ?? 'Ubicación no definida';
                        if (method_exists($mobiliario->localizacion, 'getUbicacionCompletaAttribute')) {
                            $ubicacion = $mobiliario->localizacion->ubicacion_completa;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error obteniendo ubicación: ' . $e->getMessage());
                }
                $row[] = $ubicacion;
            }
            if (in_array('fecha_baja', $this->selectedFields)) {
                $fecha = $mobiliario->fecha_baja ? $mobiliario->fecha_baja->format('d/m/Y') : 'N/A';
                $row[] = $fecha;
            }
            if (in_array('motivo_baja', $this->selectedFields)) {
                $row[] = $mobiliario->motivo_baja ?? 'No especificado';
            }
            if (in_array('estado_mobiliario', $this->selectedFields)) {
                $row[] = $mobiliario->estado_mobiliario ?? 'No especificado';
            }
            if (in_array('metodo_adquisicion', $this->selectedFields)) {
                $row[] = $mobiliario->metodo_adquisicion ?? 'No especificado';
            }
        } catch (\Exception $e) {
            Log::error('Error en EquipoBajaExport::map: ' . $e->getMessage());
            $row = array_fill(0, count($this->selectedFields), 'Error al cargar datos');
        }
        
        return $row;
    }

    /**
     * Aplica estilos al archivo Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila (encabezados)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FFE6E6'] // Color rojo claro para indicar equipos dados de baja
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}
