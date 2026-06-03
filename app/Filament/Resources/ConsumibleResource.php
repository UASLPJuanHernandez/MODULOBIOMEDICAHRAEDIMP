<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsumibleResource\Pages;
use App\Models\Consumible;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsumibleResource extends Resource
{
    protected static ?string $model = Consumible::class;

    protected static ?string $navigationIcon        = 'heroicon-o-beaker';
    protected static ?string $navigationLabel       = 'Material';
    protected static ?string $navigationGroup       = 'Inventario';
    protected static ?string $modelLabel            = 'Consumible';
    protected static ?string $pluralModelLabel      = 'Material / Consumibles';
    protected static ?int    $navigationSort        = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Consumible')
                ->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('marca')
                            ->label('Marca')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('referencia')
                            ->label('Referencia')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ]),
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
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0    => 'danger',
                        $state <= 5     => 'warning',
                        default         => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin stock (cantidad = 0)')
                    ->query(fn ($query) => $query->where('cantidad', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('stock_bajo')
                    ->label('Stock bajo (≤ 5)')
                    ->query(fn ($query) => $query->where('cantidad', '<=', 5)->where('cantidad', '>', 0))
                    ->toggle(),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar consumible')
                    ->modalDescription('¿Seguro que deseas eliminar este consumible? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('deseleccionar')
                    ->label('Quitar selección')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->action(fn () => null)
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Eliminar selección')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar consumibles seleccionados')
                    ->modalDescription('Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar todos'),
            ])
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConsumibles::route('/'),
            'create' => Pages\CreateConsumible::route('/create'),
            'view'   => Pages\ViewConsumible::route('/{record}'),
            'edit'   => Pages\EditConsumible::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
