<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Localizacion;

class LocalizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $localizaciones = [
            // Urgencias
            [
                'direccion' => 'Planta Baja',
                'division' => 'Urgencias',
                'sub_area' => 'Triage',
                'ubicacion' => 'Área de Clasificación'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Urgencias',
                'sub_area' => 'Consultorios',
                'ubicacion' => 'Consultorio 1'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Urgencias',
                'sub_area' => 'Consultorios',
                'ubicacion' => 'Consultorio 2'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Urgencias',
                'sub_area' => 'Observación',
                'ubicacion' => 'Camas de Observación'
            ],

            // Hospitalización
            [
                'direccion' => 'Primer Piso',
                'division' => 'Hospitalización',
                'sub_area' => 'Medicina Interna',
                'ubicacion' => 'Habitación 101'
            ],
            [
                'direccion' => 'Primer Piso',
                'division' => 'Hospitalización',
                'sub_area' => 'Medicina Interna',
                'ubicacion' => 'Habitación 102'
            ],
            [
                'direccion' => 'Primer Piso',
                'division' => 'Hospitalización',
                'sub_area' => 'Cirugía',
                'ubicacion' => 'Habitación 201'
            ],
            [
                'direccion' => 'Primer Piso',
                'division' => 'Hospitalización',
                'sub_area' => 'Cirugía',
                'ubicacion' => 'Habitación 202'
            ],

            // Quirófanos
            [
                'direccion' => 'Segundo Piso',
                'division' => 'Quirófanos',
                'sub_area' => 'Cirugía General',
                'ubicacion' => 'Quirófano 1'
            ],
            [
                'direccion' => 'Segundo Piso',
                'division' => 'Quirófanos',
                'sub_area' => 'Cirugía General',
                'ubicacion' => 'Quirófano 2'
            ],
            [
                'direccion' => 'Segundo Piso',
                'division' => 'Quirófanos',
                'sub_area' => 'Cirugía Especializada',
                'ubicacion' => 'Quirófano 3'
            ],

            // Laboratorio
            [
                'direccion' => 'Planta Baja',
                'division' => 'Laboratorio',
                'sub_area' => 'Química Clínica',
                'ubicacion' => 'Lab. Química'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Laboratorio',
                'sub_area' => 'Hematología',
                'ubicacion' => 'Lab. Hematología'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Laboratorio',
                'sub_area' => 'Microbiología',
                'ubicacion' => 'Lab. Microbiología'
            ],

            // Imagenología
            [
                'direccion' => 'Planta Baja',
                'division' => 'Imagenología',
                'sub_area' => 'Rayos X',
                'ubicacion' => 'Sala de Rayos X'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Imagenología',
                'sub_area' => 'Ultrasonido',
                'ubicacion' => 'Sala de Ultrasonido'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Imagenología',
                'sub_area' => 'Tomografía',
                'ubicacion' => 'Sala de TAC'
            ],

            // Consulta Externa
            [
                'direccion' => 'Planta Baja',
                'division' => 'Consulta Externa',
                'sub_area' => 'Medicina General',
                'ubicacion' => 'Consultorio A'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Consulta Externa',
                'sub_area' => 'Pediatría',
                'ubicacion' => 'Consultorio B'
            ],
            [
                'direccion' => 'Planta Baja',
                'division' => 'Consulta Externa',
                'sub_area' => 'Ginecología',
                'ubicacion' => 'Consultorio C'
            ],

            // Administración
            [
                'direccion' => 'Tercer Piso',
                'division' => 'Administración',
                'sub_area' => 'Dirección',
                'ubicacion' => 'Oficina del Director'
            ],
            [
                'direccion' => 'Tercer Piso',
                'division' => 'Administración',
                'sub_area' => 'Recursos Humanos',
                'ubicacion' => 'Oficina de RH'
            ],
            [
                'direccion' => 'Tercer Piso',
                'division' => 'Administración',
                'sub_area' => 'Activo Fijo',
                'ubicacion' => 'Oficina de Activo Fijo'
            ],

            // Almacén
            [
                'direccion' => 'Sótano',
                'division' => 'Almacén',
                'sub_area' => 'Almacén General',
                'ubicacion' => 'Área de Medicamentos'
            ],
            [
                'direccion' => 'Sótano',
                'division' => 'Almacén',
                'sub_area' => 'Almacén General',
                'ubicacion' => 'Área de Material Médico'
            ],
            [
                'direccion' => 'Sótano',
                'division' => 'Almacén',
                'sub_area' => 'Almacén General',
                'ubicacion' => 'Área de Equipos'
            ],
        ];

        foreach ($localizaciones as $localizacion) {
            Localizacion::create($localizacion);
        }
    }
}
