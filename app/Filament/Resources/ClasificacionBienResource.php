<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClasificacionBienResource\Pages;
use App\Filament\Resources\ClasificacionBienResource\RelationManagers;
use App\Models\ClasificacionBien;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClasificacionBienResource extends Resource
{
    protected static ?string $model = ClasificacionBien::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('grupo')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('subgrupo')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('clase')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nombre_grupo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descripcion_clase')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grupo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subgrupo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clase')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_grupo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasificacionBiens::route('/'),
            'create' => Pages\CreateClasificacionBien::route('/create'),
            'edit' => Pages\EditClasificacionBien::route('/{record}/edit'),
        ];
    }
}
