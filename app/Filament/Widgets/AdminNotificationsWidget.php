<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminNotificationsWidget extends BaseWidget
{
	protected static ?int $sort = 9999;
	public static function canView(): bool { return false; }
	protected function getStats(): array { return [ Stat::make('Obsoleto', 0)->description('Widget eliminado')->color('gray') ]; }
}
