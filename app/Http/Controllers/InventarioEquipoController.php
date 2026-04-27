<?php

namespace App\Http\Controllers;

use App\Models\InventarioEquipo;
use App\Models\InventarioEquipoHistorial;
use App\Models\ValeInventario;
use App\Services\ValeEntregaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventarioEquipoController extends Controller
{
    // ── Vales ──────────────────────────────────────────────────────────────

    public function valeEntregaDocx(InventarioEquipo $equipo)
    {
        $service = new ValeEntregaService();
        $tmpPath = $service->generarEntrega($equipo);

        return $this->descargarDocx($tmpPath, 'vale-entrega-' . $this->slug($equipo));
    }

    public function valeRetiroDocx(InventarioEquipo $equipo)
    {
        $service = new ValeEntregaService();
        $tmpPath = $service->generarRetiro($equipo);

        return $this->descargarDocx($tmpPath, 'vale-retiro-' . $this->slug($equipo));
    }

    /**
     * Descarga un vale ya registrado (re-generado desde los datos cacheados).
     */
    public function valeRedescargar(ValeInventario $vale)
    {
        $service = new ValeEntregaService();
        $tmpPath = $service->regenerar($vale);

        $tipo = $vale->tipo === 'retiro' ? 'retiro' : 'entrega';
        $inv  = $vale->numero_inventario
            ? str_replace(['/', '\\', ' '], '-', $vale->numero_inventario)
            : $vale->id;

        return $this->descargarDocx($tmpPath, "vale-{$tipo}-{$inv}");
    }

    // ── Historial PDF ──────────────────────────────────────────────────────

    public function historialPdf(InventarioEquipo $equipo)
    {
        $historiales = $equipo->historiales()->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.historial-inventario', [
            'equipo'          => $equipo,
            'historiales'     => $historiales,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('historial-inventario-' . $this->slug($equipo) . '.pdf');
    }

    public function historialGeneralPdf(Request $request)
    {
        $query = InventarioEquipoHistorial::with('inventarioEquipo')->latest();

        if ($request->filled('tipo'))  $query->where('tipo_evento', $request->tipo);
        if ($request->filled('desde')) $query->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta')) $query->whereDate('created_at', '<=', $request->hasta);
        if ($request->boolean('hoy'))  $query->whereDate('created_at', today());

        $pdf = Pdf::loadView('pdf.historial-general-inventario', [
            'historiales'     => $query->get(),
            'filtros'         => [
                'desde' => $request->filled('desde') ? Carbon::parse($request->desde)->format('d/m/Y') : null,
                'hasta' => $request->filled('hasta') ? Carbon::parse($request->hasta)->format('d/m/Y') : null,
            ],
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('historial-general-inventario-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    protected function descargarDocx(string $tmpPath, string $nombre)
    {
        return response()->download($tmpPath, $nombre . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    protected function slug(InventarioEquipo $equipo): string
    {
        return $equipo->numero_inventario
            ? str_replace(['/', '\\', ' '], '-', $equipo->numero_inventario)
            : (string) $equipo->id;
    }
}
