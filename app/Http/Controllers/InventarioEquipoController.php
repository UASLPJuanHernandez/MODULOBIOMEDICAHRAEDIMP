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

    public function valePreview(ValeInventario $vale)
    {
        $pdf = Pdf::loadView('pdf.vale-inventario-preview', compact('vale'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('vale-preview-' . $vale->id . '.pdf');
    }

    public function valeDescargarPdf(ValeInventario $vale)
    {
        $pdf = Pdf::loadView('pdf.vale-inventario-preview', compact('vale'))
            ->setPaper('letter', 'portrait');

        $tipo = $vale->tipo === 'retiro' ? 'retiro' : 'entrega';
        $inv  = $vale->numero_inventario
            ? str_replace(['/', '\\', ' '], '-', $vale->numero_inventario)
            : $vale->id;

        return $pdf->download("vale-{$tipo}-{$inv}.pdf");
    }

    public function concretarVale(\Illuminate\Http\Request $request, ValeInventario $vale)
    {
        $request->validate([
            'quien_recibe' => 'nullable|string|max:255',
            'cargo_recibe' => 'nullable|string|max:255',
            'observaciones'=> 'nullable|string|max:1000',
            'firma_imagen' => 'nullable|string',
            'jefe_id'      => 'required|exists:personal_reportante,id',
        ]);

        $vale->update([
            'estado'        => 'en_firma',
            'jefe_id'       => $request->jefe_id,
            'quien_recibe'  => $request->quien_recibe,
            'cargo_recibe'  => $request->cargo_recibe,
            'observaciones' => $request->observaciones,
            'firma_imagen'  => $request->firma_imagen,
        ]);

        return response()->json(['ok' => true]);
    }

    public function firmarValeJefe(\Illuminate\Http\Request $request, ValeInventario $vale)
    {
        $personal = \Illuminate\Support\Facades\Auth::guard('personal')->user();

        abort_unless($vale->jefe_id === $personal->id, 403);
        abort_unless($vale->estado === 'en_firma', 400);

        $vale->update([
            'estado'        => 'culminado',
            'firmado_at'    => now(),
            'concretado_at' => now(),
        ]);

        return back()->with('vale_firmado', $vale->id);
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
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $historiales = $equipo->historiales()->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.historial-inventario', [
            'equipo'          => $equipo,
            'historiales'     => $historiales,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
        ])->setPaper('A4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'historial-inventario-' . $this->slug($equipo) . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function historialGeneralPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

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

        $nombre = 'historial-general-inventario-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $nombre, [
            'Content-Type' => 'application/pdf',
        ]);
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
