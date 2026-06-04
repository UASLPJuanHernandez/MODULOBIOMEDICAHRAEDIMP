<?php

use App\Models\User;
use App\Models\InventarioEquipo;
use App\Models\Consumible;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

// ── Inventario de equipos ────────────────────────────────────────────────────

test('la lista de inventario requiere autenticación', function () {
    auth()->logout();

    $this->get(route('filament.admin.resources.inventario-equipos.index'))
        ->assertRedirect();
});

test('admin autenticado puede ver la lista de inventario', function () {
    $this->get(route('filament.admin.resources.inventario-equipos.index'))
        ->assertOk();
});

test('se puede crear un equipo en inventario', function () {
    $equipo = InventarioEquipo::create([
        'equipo'            => 'Ventilador mecánico',
        'marca'             => 'Dräger',
        'modelo'            => 'Evita V300',
        'numero_inventario' => 'INV-2024-001',
        'area'              => 'UCIA',
        'estado'            => 'activo',
    ]);

    expect($equipo->id)->toBeInt()
        ->and($equipo->equipo)->toBe('Ventilador mecánico')
        ->and(InventarioEquipo::count())->toBe(1);
});

test('el número de inventario puede buscarse', function () {
    InventarioEquipo::create([
        'equipo'            => 'Bomba de infusión',
        'numero_inventario' => 'INV-BOMBA-01',
        'area'              => 'Oncología adultos',
        'estado'            => 'activo',
    ]);

    $resultado = InventarioEquipo::where('numero_inventario', 'INV-BOMBA-01')->first();

    expect($resultado)->not->toBeNull()
        ->and($resultado->equipo)->toBe('Bomba de infusión');
});

test('la vista de un equipo requiere autenticación', function () {
    auth()->logout();

    $equipo = InventarioEquipo::create([
        'equipo' => 'Electrocardiógrafo',
        'estado' => 'activo',
    ]);

    $this->get(route('filament.admin.resources.inventario-equipos.view', $equipo))
        ->assertRedirect();
});

// ── Consumibles ──────────────────────────────────────────────────────────────

test('se puede registrar un consumible con stock inicial', function () {
    $consumible = Consumible::create([
        'nombre'   => 'Jeringas 5ml',
        'marca'    => 'BD',
        'cantidad' => 200,
    ]);

    expect($consumible->cantidad)->toBe(200);
});

test('el stock de consumible se puede decrementar', function () {
    $consumible = Consumible::create([
        'nombre'   => 'Gasas estériles',
        'cantidad' => 100,
    ]);

    $consumible->decrement('cantidad', 30);

    expect($consumible->fresh()->cantidad)->toBe(70);
});

test('la lista de consumibles requiere autenticación', function () {
    auth()->logout();

    $this->get(route('filament.admin.resources.consumibles.index'))
        ->assertRedirect();
});
