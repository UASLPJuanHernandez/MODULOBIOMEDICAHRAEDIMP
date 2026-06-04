<?php

use App\Models\User;
use App\Models\PersonalReportante;

// ── Admin login ──────────────────────────────────────────────────────────────

test('la página de login carga correctamente', function () {
    $this->get('/simple-login')->assertOk();
});

test('admin puede iniciar sesión con credenciales correctas', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/simple-login', [
        'email'    => $user->email,
        'password' => 'password123',
    ])->assertRedirect('/biomedica');
});

test('admin no puede iniciar sesión con contraseña incorrecta', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correcta'),
    ]);

    $this->post('/simple-login', [
        'email'    => $user->email,
        'password' => 'incorrecta',
    ])->assertSessionHasErrors('email');
});

test('usuario autenticado es redirigido a /biomedica desde login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/simple-login')
        ->assertRedirect('/biomedica');
});

test('usuario no autenticado es redirigido al login al acceder a /biomedica', function () {
    $this->get('/biomedica')->assertRedirect();
});

// ── Portal personal ──────────────────────────────────────────────────────────

test('la página de login del portal carga correctamente', function () {
    $this->get('/reportes')->assertOk();
});

test('personal reportante puede iniciar sesión en el portal', function () {
    $personal = PersonalReportante::factory()->create([
        'password' => bcrypt('pass123'),
        'estado'   => 'aprobado',
    ]);

    $this->post('/reportes/login', [
        'numero_empleado' => $personal->numero_empleado,
        'password'        => 'pass123',
    ])->assertRedirect();
});

test('personal no aprobado no puede acceder al portal', function () {
    $personal = PersonalReportante::factory()->create([
        'password' => bcrypt('pass123'),
        'estado'   => 'pendiente',
    ]);

    $this->post('/reportes/login', [
        'numero_empleado' => $personal->numero_empleado,
        'password'        => 'pass123',
    ])->assertSessionHasErrors();
});
