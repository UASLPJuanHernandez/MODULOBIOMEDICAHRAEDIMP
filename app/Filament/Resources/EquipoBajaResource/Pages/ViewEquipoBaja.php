<?php

namespace App\Filament\Resources\EquipoBajaResource\Pages;

use App\Filament\Resources\EquipoBajaResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipoBaja extends ViewRecord
{
    protected static string $resource = EquipoBajaResource::class;
    
    protected static ?string $title = 'Ver Equipo Dado de Baja';
}
