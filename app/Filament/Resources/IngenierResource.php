<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngenierResource\Pages;
use App\Models\Ingeniero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use App\Filament\Forms\Components\FirmaPad;

class IngenierResource extends Resource
{
    protected static ?string $model = Ingeniero::class;

    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Ingenieros';
    protected static ?string $modelLabel      = 'Ingeniero';
    protected static ?string $pluralModelLabel = 'Ingenieros';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del ingeniero')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('nombre')
                                ->label('Nombre completo')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('cargo')
                                ->label('Cargo')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('cedula_profesional')
                                ->label('Cédula profesional')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('email')
                                ->label('Correo electrónico')
                                ->email()
                                ->maxLength(255),

                            Forms\Components\Toggle::make('activo')
                                ->label('Activo')
                                ->default(true)
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Firma')
                    ->description('Dibuja la firma con el mouse/trackpad o sube una imagen PNG.')
                    ->schema([
                        FirmaPad::make('firma_svg')
                            ->label('Firma'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('cedula_profesional')
                    ->label('Cédula')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIngenieros::route('/'),
            'create' => Pages\CreateIngeniero::route('/create'),
            'edit'   => Pages\EditIngeniero::route('/{record}/edit'),
        ];
    }
}
