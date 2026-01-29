<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Mobiliario;

echo "╔═══════════════════════════════════════════════════════════════════╗\n";
echo "║           VERIFICACIÓN DE REGISTROS IMPORTADOS                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

$total = Mobiliario::count();
echo "📊 Total de mobiliarios en la base de datos: {$total}\n\n";

echo "🔍 Últimos 6 registros importados:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$ultimos = Mobiliario::orderBy('id', 'desc')
    ->limit(6)
    ->get(['id', 'numero_control', 'descripcion', 'marca', 'modelo', 'precio']);

foreach ($ultimos as $mobiliario) {
    echo "\n✓ ID: {$mobiliario->id}\n";
    echo "  • Número de control: {$mobiliario->numero_control}\n";
    echo "  • Descripción: {$mobiliario->descripcion}\n";
    echo "  • Marca: {$mobiliario->marca}\n";
    echo "  • Modelo: {$mobiliario->modelo}\n";
    echo "  • Precio: $" . number_format($mobiliario->precio, 2) . "\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Verificación completada\n";
