<?php

namespace App\Exports;

use App\Models\InventarioEquipo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class InventarioEquipoExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    WithEvents
{
    protected Carbon $fechaExportacion;
    protected ?Collection $registros;

    public function __construct(?Collection $registros = null)
    {
        $this->fechaExportacion = Carbon::now();
        $this->registros = $registros;
    }

    public function title(): string
    {
        return 'Inventario';
    }

    public function collection(): Collection
    {
        if ($this->registros !== null) {
            return $this->registros;
        }

        return InventarioEquipo::orderBy('area')
            ->orderBy('equipo')
            ->orderBy('numero_inventario')
            ->get();
    }

    public function headings(): array
    {
        return [
            // Fila 1: título del reporte (se fusiona en AfterSheet)
            ['INVENTARIO FUNCIONAL DE EQUIPO MÉDICO — HRAEDIMP', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            // Fila 2: fecha de exportación
            ['Fecha de exportación: ' . $this->fechaExportacion->format('d/m/Y H:i'), '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            // Fila 3: encabezados de columnas
            [
                'No. Inventario',
                'CLUES',
                'Unidad Médica',
                'Área / Especialidad',
                'Ubicación Específica',
                'Clave CSG',
                'Equipo',
                'Equipo Alternativo',
                'Marca',
                'Modelo',
                'No. de Serie',
                'Propiedad',
                'Condiciones',
                'Estatus',
                'Causa No Funcionamiento',
                'Fecha Adquisición',
                'Año Fabricación',
                'Requerimientos',
                'Frecuencia Mantenimiento',
                'Tipo Mantenimiento',
                'Contrato Mant.',
                'Fin Vida Útil (EOL)',
                'Garantía',
                'Fin Garantía',
                'Tiene Contrato',
                'No. Contrato',
                'Proveedor Mantenimiento',
                'Inicio Póliza',
                'Fin Póliza',
                'MP / Año',
                'Último MP',
                'Siguiente MP',
                'Costo Contrato',
                'Observaciones',
                'Fecha Creación Registro',
                'Última Modificación',
            ],
        ];
    }

    public function map($equipo): array
    {
        return [
            $equipo->numero_inventario ?? 'N/A',
            $equipo->clues ?? 'N/A',
            $equipo->unidad_medica ?? 'N/A',
            $equipo->area ?? 'N/A',
            $equipo->ubicacion_especifica ?? 'N/A',
            $equipo->clave_cbsg ?? 'N/A',
            $equipo->equipo ?? 'N/A',
            $equipo->equipo_alternativo ?? 'N/A',
            $equipo->marca ?? 'N/A',
            $equipo->modelo ?? 'N/A',
            $equipo->numero_serie ?? 'N/A',
            $equipo->propiedad ?? 'N/A',
            $equipo->condiciones ?? 'N/A',
            $equipo->estatus ?? 'N/A',
            $equipo->causa_no_funcionamiento ?? 'N/A',
            $equipo->fecha_adquisicion?->format('d/m/Y') ?? 'N/A',
            $equipo->anio_fabricacion ?? 'N/A',
            $equipo->requerimientos ?? 'N/A',
            $equipo->frecuencia_mantenimiento ?? 'N/A',
            $equipo->tipo_mantenimiento ?? 'N/A',
            $equipo->contrato_mantenimiento ?? 'N/A',
            $equipo->fin_vida_util ? 'SÍ' : 'NO',
            $equipo->garantia ? 'SÍ' : 'NO',
            $equipo->fin_garantia?->format('d/m/Y') ?? 'N/A',
            $equipo->tiene_contrato ? 'SÍ' : 'NO',
            $equipo->numero_contrato ?? 'N/A',
            $equipo->proveedor_mantenimiento ?? 'N/A',
            $equipo->inicio_poliza?->format('d/m/Y') ?? 'N/A',
            $equipo->fin_poliza?->format('d/m/Y') ?? 'N/A',
            $equipo->cantidad_mp_anio ?? 'N/A',
            $equipo->ultimo_mp?->format('d/m/Y') ?? 'N/A',
            $equipo->siguiente_mp?->format('d/m/Y') ?? 'N/A',
            $equipo->costo_contrato ?? 'N/A',
            $equipo->observaciones ?? '',
            $equipo->created_at?->format('d/m/Y H:i') ?? '',
            $equipo->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,  // No. Inventario
            'B' => 14,  // CLUES
            'C' => 32,  // Unidad Médica
            'D' => 30,  // Área
            'E' => 25,  // Ubicación
            'F' => 16,  // Clave CSG
            'G' => 35,  // Equipo
            'H' => 25,  // Equipo Alternativo
            'I' => 16,  // Marca
            'J' => 18,  // Modelo
            'K' => 22,  // No. Serie
            'L' => 14,  // Propiedad
            'M' => 14,  // Condiciones
            'N' => 24,  // Estatus
            'O' => 30,  // Causa No Funcionamiento
            'P' => 16,  // Fecha Adquisición
            'Q' => 14,  // Año Fabricación
            'R' => 35,  // Requerimientos
            'S' => 22,  // Frecuencia Mant.
            'T' => 18,  // Tipo Mant.
            'U' => 16,  // Contrato Mant.
            'V' => 14,  // EOL
            'W' => 12,  // Garantía
            'X' => 14,  // Fin Garantía
            'Y' => 14,  // Tiene Contrato
            'Z' => 30,  // No. Contrato
            'AA' => 35, // Proveedor
            'AB' => 14, // Inicio Póliza
            'AC' => 14, // Fin Póliza
            'AD' => 12, // MP/Año
            'AE' => 14, // Último MP
            'AF' => 14, // Siguiente MP
            'AG' => 18, // Costo Contrato
            'AH' => 40, // Observaciones
            'AI' => 18, // Fecha Creación
            'AJ' => 18, // Última Modificación
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'AJ';

        return [
            // Fila 1: título principal
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Fila 2: fecha
            2 => [
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '555555']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F4F8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            // Fila 3: encabezados de columnas
            3 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'AJ';

                // Fusionar celdas del título y fecha
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                // Altura de filas
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(35);

                // Freeze encabezados
                $sheet->freezePane('A4');

                // Filas de datos: texto en negro, bordes finos, alineación
                $totalRows = $sheet->getHighestRow();
                if ($totalRows >= 4) {
                    $sheet->getStyle("A4:{$lastCol}{$totalRows}")->applyFromArray([
                        'font' => ['size' => 9],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => false],
                    ]);

                    // Filas alternas (zebra)
                    for ($row = 4; $row <= $totalRows; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFF6FF']],
                            ]);
                        }
                    }

                    // Colorear columna Estatus (N = col 14)
                    for ($row = 4; $row <= $totalRows; $row++) {
                        $estatus = strtoupper(trim($sheet->getCell("N{$row}")->getValue() ?? ''));
                        if (str_contains($estatus, 'COMPLETO') || str_contains($estatus, 'FUNCIONANDO')) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => '166534'], 'bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                            ]);
                        } elseif (str_contains($estatus, 'PARCIAL')) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => '92400E'], 'bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEF3C7']],
                            ]);
                        } elseif (str_contains($estatus, 'FUERA') || str_contains($estatus, 'DISFUNCIONAL') || str_contains($estatus, 'NO FUNCIONA')) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['color' => ['rgb' => '991B1B'], 'bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEE2E2']],
                            ]);
                        }
                    }
                }

                // Auto-filter en la fila de encabezados
                $sheet->setAutoFilter("A3:{$lastCol}3");

                // Orientación horizontal para impresión
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
