<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

// Stub seguro: evita errores si alguna vista antigua intenta cargarlo.
class TestWidget extends BaseWidget
{
	protected static ?int $sort = 9999; // Al final
	public static function canView(): bool { return false; }
	protected function getStats(): array { return [ Stat::make('Obsoleto', 0)->description('Widget eliminado')->color('gray') ]; }
}
