<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class CambiarFondoLogin extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon  = null;
    protected static bool    $shouldRegisterNavigation = false;
    protected static string  $view = 'filament.pages.cambiar-fondo-login';

    public $imagen               = null;
    public $imagenSalvapantallas = null;

    protected function getViewData(): array
    {
        return [
            'fondoActual'          => static::getFondoUrl(),
            'salvapantallasActual' => static::getSalvapantallasUrl(),
        ];
    }

    public static function getFondoUrl(): ?string
    {
        $path = storage_path('app/login-bg-path.txt');
        if (!file_exists($path)) return null;

        $stored = trim(file_get_contents($path));
        if (!$stored || !Storage::disk('public')->exists($stored)) return null;

        return '/storage/' . $stored;
    }

    public function guardar(): void
    {
        $this->validate(['imagen' => 'required|image|max:5120']);

        $ext  = $this->imagen->getClientOriginalExtension();
        $nombre = 'login-bg.' . $ext;

        // Eliminar fondo anterior
        $pathFile = storage_path('app/login-bg-path.txt');
        if (file_exists($pathFile)) {
            $prev = trim(file_get_contents($pathFile));
            if ($prev) Storage::disk('public')->delete($prev);
        }

        $this->imagen->storeAs('', $nombre, 'public');
        file_put_contents($pathFile, $nombre);

        $this->imagen = null;

        Notification::make()->title('Fondo actualizado correctamente')->success()->send();
    }

    public function eliminar(): void
    {
        $pathFile = storage_path('app/login-bg-path.txt');
        if (file_exists($pathFile)) {
            $prev = trim(file_get_contents($pathFile));
            if ($prev) Storage::disk('public')->delete($prev);
            unlink($pathFile);
        }

        Notification::make()->title('Fondo eliminado — se usará el degradado por defecto')->success()->send();
    }

    // ─── Salvapantallas ────────────────────────────────────────────────────────

    public static function getSalvapantallasUrl(): ?string
    {
        $path = storage_path('app/screensaver-path.txt');
        if (!file_exists($path)) return null;

        $stored = trim(file_get_contents($path));
        if (!$stored || !Storage::disk('public')->exists($stored)) return null;

        return '/storage/' . $stored;
    }

    public function guardarSalvapantallas(): void
    {
        $this->validate(['imagenSalvapantallas' => 'required|image|max:10240']);

        $ext    = $this->imagenSalvapantallas->getClientOriginalExtension();
        $nombre = 'screensaver.' . $ext;

        $pathFile = storage_path('app/screensaver-path.txt');
        if (file_exists($pathFile)) {
            $prev = trim(file_get_contents($pathFile));
            if ($prev) Storage::disk('public')->delete($prev);
        }

        $this->imagenSalvapantallas->storeAs('', $nombre, 'public');
        file_put_contents($pathFile, $nombre);

        $this->imagenSalvapantallas = null;

        Notification::make()->title('Salvapantallas actualizado correctamente')->success()->send();
    }

    public function eliminarSalvapantallas(): void
    {
        $pathFile = storage_path('app/screensaver-path.txt');
        if (file_exists($pathFile)) {
            $prev = trim(file_get_contents($pathFile));
            if ($prev) Storage::disk('public')->delete($prev);
            unlink($pathFile);
        }

        Notification::make()->title('Salvapantallas eliminado')->success()->send();
    }
}
