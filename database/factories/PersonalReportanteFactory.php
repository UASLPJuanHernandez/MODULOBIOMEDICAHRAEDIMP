<?php

namespace Database\Factories;

use App\Models\PersonalReportante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PersonalReportanteFactory extends Factory
{
    protected $model = PersonalReportante::class;

    public function definition(): array
    {
        return [
            'nombre'          => $this->faker->name(),
            'numero_empleado' => $this->faker->unique()->numerify('EMP-####'),
            'servicio'        => $this->faker->randomElement(['Urgencias', 'UCI', 'Pediatría']),
            'password'        => Hash::make('password'),
            'estado'          => 'aprobado',
            'es_jefe_servicio'=> false,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(['estado' => 'pendiente']);
    }

    public function jefe(string $area = 'Urgencias'): static
    {
        return $this->state([
            'es_jefe_servicio'  => true,
            'area_jefe_servicio'=> $area,
        ]);
    }
}
