<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClasificacionBien;

class ClasificacionBienesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clasificaciones = [
            // Grupo 5: Bienes Muebles, Inmuebles e Intangibles
            // Subgrupo 1 - Mobiliario y Equipo de Administración
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Muebles de oficina y estantería'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 2,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Muebles, excepto de oficina y estantería'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 3,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Bienes artísticos, culturales y científicos'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 4,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Objetos de valor'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 5,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipos de cómputo y tecnologías de la información'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 1,
                'clase' => 6,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Otros mobiliarios y equipos de administración'
            ],

            // Subgrupo 2 - Mobiliario y Equipo Educacional y Recreativo
            [
                'grupo' => 5,
                'subgrupo' => 2,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipos y aparatos audiovisuales'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 2,
                'clase' => 2,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Aparatos deportivos'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 2,
                'clase' => 3,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Cámaras fotográficas y de video'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 2,
                'clase' => 4,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Otro mobiliario y equipo educacional y recreativo'
            ],

            // Subgrupo 3 - Equipo e Instrumental Médico y de Laboratorio
            [
                'grupo' => 5,
                'subgrupo' => 3,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipo médico y de laboratorio'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 3,
                'clase' => 2,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Instrumental médico y de laboratorio'
            ],

            // Subgrupo 4 - Vehículos y Equipo de Transporte
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Vehículos y equipo terrestres'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 2,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Carrocerías y remolques'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 3,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipo aeroespacial'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 4,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipo ferroviario'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 5,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Embarcaciones'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 4,
                'clase' => 6,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Otros equipos de transporte'
            ],

            // Subgrupo 5 - Equipo de Defensa y Seguridad
            [
                'grupo' => 5,
                'subgrupo' => 5,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipo de defensa y seguridad'
            ],

            // Subgrupo 6 - Maquinaria, Otros Equipos y Herramientas
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 1,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Maquinaria y equipo agropecuario'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 2,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Maquinaria y equipo industrial'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 3,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Maquinaria y equipo de construcción'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 4,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Sistemas de aire acondicionado, calefacción y de refrigeración industrial y comercial'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 5,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipo de comunicación y telecomunicación'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 6,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Equipos de generación eléctrica, aparatos y accesorios eléctricos'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 7,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Herramientas y máquinas-herramienta'
            ],
            [
                'grupo' => 5,
                'subgrupo' => 6,
                'clase' => 9,
                'nombre_grupo' => 'Bienes Muebles, Inmuebles e Intangibles',
                'descripcion_clase' => 'Otros equipos'
            ],

            // Grupo 6: Bienes Inmuebles
            [
                'grupo' => 6,
                'subgrupo' => 8,
                'clase' => 3,
                'nombre_grupo' => 'Bienes Inmuebles',
                'descripcion_clase' => 'Edificios no habitacionales'
            ],

            // Grupo 7: Activos Biológicos
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 1,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Bovinos'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 2,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Porcinos'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 3,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Aves'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 4,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Ovinos y caprinos'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 5,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Peces y acuicultura'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 6,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Equinos'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 7,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Especies menores'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 8,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Árboles y plantas'
            ],
            [
                'grupo' => 7,
                'subgrupo' => 7,
                'clase' => 9,
                'nombre_grupo' => 'Activos Biológicos',
                'descripcion_clase' => 'Otros activos biológicos'
            ],
        ];

        foreach ($clasificaciones as $clasificacion) {
            ClasificacionBien::create($clasificacion);
        }
    }
}
