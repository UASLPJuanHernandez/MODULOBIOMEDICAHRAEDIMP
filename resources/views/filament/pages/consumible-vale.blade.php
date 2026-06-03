<x-filament-panels::page>

    <x-filament-panels::form wire:submit="generar">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="[
                \Filament\Actions\Action::make('generar')
                    ->label('Generar Vale')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->submit('generar'),
            ]"
        />
    </x-filament-panels::form>

</x-filament-panels::page>
