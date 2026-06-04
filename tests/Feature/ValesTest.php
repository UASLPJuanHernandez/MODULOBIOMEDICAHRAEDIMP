<?php

use App\Models\User;
use App\Models\Consumible;
use App\Models\ValeInventario;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

// ── Vales de consumible ──────────────────────────────────────────────────────

test('se puede crear un vale de consumible y descuenta el stock', function () {
    $consumible = Consumible::create([
        'nombre'      => 'Guantes de látex',
        'descripcion' => 'Caja 100 unidades',
        'marca'       => 'Medline',
        'referencia'  => 'GL-100',
        'cantidad'    => 50,
    ]);

    $vale = ValeInventario::create([
        'tipo'               => 'entrega',
        'consumible_id'      => $consumible->id,
        'cantidad_entregada' => 10,
        'equipo_nombre'      => $consumible->nombre,
        'area'               => 'Urgencias',
        'usuario_nombre'     => $this->admin->name,
        'estado'             => 'pendiente',
    ]);

    $consumible->decrement('cantidad', 10);

    expect($vale->consumible_id)->toBe($consumible->id)
        ->and($consumible->fresh()->cantidad)->toBe(40);
});

test('un vale de consumible tiene estado pendiente al crearse', function () {
    $consumible = Consumible::create([
        'nombre'   => 'Alcohol gel',
        'cantidad' => 20,
    ]);

    $vale = ValeInventario::create([
        'tipo'               => 'entrega',
        'consumible_id'      => $consumible->id,
        'cantidad_entregada' => 5,
        'equipo_nombre'      => 'Alcohol gel',
        'usuario_nombre'     => 'Admin',
        'estado'             => 'pendiente',
    ]);

    expect($vale->estado)->toBe('pendiente');
});

test('la ruta de PDF de vale de consumible requiere autenticación', function () {
    auth()->logout();

    $consumible = Consumible::create(['nombre' => 'Test', 'cantidad' => 1]);

    $vale = ValeInventario::create([
        'tipo'           => 'entrega',
        'consumible_id'  => $consumible->id,
        'equipo_nombre'  => 'Test',
        'usuario_nombre' => 'Admin',
        'estado'         => 'pendiente',
    ]);

    $this->get(route('admin.consumible.vale.pdf', $vale))
        ->assertRedirect();
});

// ── Vales de equipo ──────────────────────────────────────────────────────────

test('la página de crear vale requiere autenticación', function () {
    auth()->logout();

    $this->get('/biomedica/vale-inventarios/create')->assertRedirect();
});

test('un vale de equipo se puede crear con los datos mínimos', function () {
    $vale = ValeInventario::create([
        'tipo'               => 'entrega',
        'equipo_nombre'      => 'Monitor de signos vitales',
        'numero_inventario'  => 'INV-001',
        'area'               => 'Urgencias',
        'usuario_nombre'     => $this->admin->name,
        'estado'             => 'pendiente',
    ]);

    expect($vale->id)->toBeInt()
        ->and($vale->tipo)->toBe('entrega')
        ->and($vale->estado)->toBe('pendiente');
});

test('un vale culminado mantiene su estado', function () {
    $vale = ValeInventario::create([
        'tipo'           => 'entrega',
        'equipo_nombre'  => 'Desfibrilador',
        'usuario_nombre' => 'Admin',
        'estado'         => 'culminado',
    ]);

    expect($vale->estado)->toBe('culminado');
});

test('no se puede crear un vale con consumible_id inválido', function () {
    expect(fn () => ValeInventario::create([
        'tipo'          => 'entrega',
        'consumible_id' => 9999,
        'equipo_nombre' => 'Test',
        'usuario_nombre'=> 'Admin',
        'estado'        => 'pendiente',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
