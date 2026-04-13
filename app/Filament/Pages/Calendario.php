<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Calendario extends Page
{
    protected static string $view = 'filament.pages.calendario';

    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Calendario';
    protected static ?string $title           = 'Calendario';
    protected static ?int    $navigationSort  = -2;

    public function getHeading(): string
    {
        return '';
    }
}
