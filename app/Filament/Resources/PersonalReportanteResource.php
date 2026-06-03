<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalReportanteResource\Pages;
use App\Models\PersonalReportante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonalReportanteResource extends Resource
{
    protected static ?string $model = PersonalReportante::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Personal reportante';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $modelLabel = 'Personal';
    protected static ?string $pluralModelLabel = 'Personal reportante';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->label('Nombre')->disabled(),
            Forms\Components\TextInput::make('numero_empleado')->label('Número de empleado')->disabled(),
            Forms\Components\TextInput::make('servicio')->label('Servicio / Área')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_empleado')
                    ->label('No. Empleado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('servicio')
                    ->label('Servicio / Área')
                    ->searchable(),
                Tables\Columns\IconColumn::make('es_jefe_servicio')
                    ->label('Jefe de Servicio')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('area_jefe_servicio')
                    ->label('Área (jefe)')
                    ->placeholder('—')
                    ->searchable()
                    ->visible(fn () => true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitó')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonalReportante::route('/'),
        ];
    }
}
