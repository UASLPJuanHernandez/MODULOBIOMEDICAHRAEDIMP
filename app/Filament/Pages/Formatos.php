<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Formatos extends Page
{
    protected static string $view = 'filament.pages.formatos';

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Formatos';
    protected static ?string $title           = 'Formatos';
    protected static ?int    $navigationSort  = 0;

    public function getHeading(): string
    {
        return '';
    }
}
