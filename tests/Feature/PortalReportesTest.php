<?php

use App\Models\PersonalReportante;
use App\Models\ReportePizarron;
use Illuminate\Support\Facades\Hash;

// ── Acceso al portal ─────────────────────────────────────────────────────────

test('la página principal del portal carga', function () {
    $this->get('/reportes')->assertOk();
});

test('personal no autenticado no puede acceder a /reportes/enviar', function () {
    $this->get(route('portal.reportes.form'))->assertRedirect();
});

test('personal aprobado puede iniciar sesión en el portal', function () {
    $personal = PersonalReportante::factory()->create([
        'password' => Hash::make('clave123'),
        'estado'   => 'aprobado',
    ]);

    $this->post(route('portal.login.submit'), [
        'numero_empleado' => $personal->numero_empleado,
        'password'        => 'clave123',
    ])->assertRedirect(route('portal.reportes.form'));
});

test('personal con estado pendiente no puede iniciar sesión', function () {
    $personal = PersonalReportante::factory()->pendiente()->create([
        'password' => Hash::make('clave123'),
    ]);

    $this->post(route('portal.login.submit'), [
        'numero_empleado' => $personal->numero_empleado,
        'password'        => 'clave123',
    ])->assertSessionHasErrors('numero_empleado');
});

test('credenciales incorrectas devuelven error', function () {
    $personal = PersonalReportante::factory()->create([
        'password' => Hash::make('correcta'),
    ]);

    $this->post(route('portal.login.submit'), [
        'numero_empleado' => $personal->numero_empleado,
        'password'        => 'incorrecta',
    ])->assertSessionHasErrors('numero_empleado');
});

// ── Reportes pizarrón ────────────────────────────────────────────────────────

test('se puede crear un reporte en el pizarrón', function () {
    $reporte = ReportePizarron::create([
        'titulo'      => 'Falla equipo UCI',
        'equipo'      => 'Carro de paro',
        'ubicacion'   => 'Urgencias',
        'descripcion' => 'No enciende',
        'prioridad'   => 'urgencia',
        'estado'      => 'pendiente',
        'concretado'  => false,
    ]);

    expect($reporte->id)->toBeInt()
        ->and($reporte->prioridad)->toBe('urgencia')
        ->and($reporte->estado)->toBe('pendiente');
});

test('el pizarrón solo muestra reportes activos', function () {
    ReportePizarron::create([
        'titulo'     => 'Reporte activo',
        'equipo'     => 'Equipo activo',
        'ubicacion'  => 'UCI',
        'descripcion'=> 'Descripción',
        'estado'     => 'pendiente',
        'prioridad'  => 'media',
        'concretado' => false,
    ]);

    ReportePizarron::create([
        'titulo'     => 'Reporte culminado',
        'equipo'     => 'Equipo culminado',
        'ubicacion'  => 'UCI',
        'descripcion'=> 'Descripción',
        'estado'     => 'completado',
        'prioridad'  => 'baja',
        'concretado' => true,
    ]);

    $activos = ReportePizarron::activos()->count();

    expect($activos)->toBe(1);
});

test('la ruta del pizarrón standalone requiere autenticación', function () {
    $this->get(route('pizarron.standalone'))->assertRedirect();
});

test('la página de firmas del portal requiere autenticación de personal', function () {
    $this->get('/reportes/firmas')->assertRedirect();
});
