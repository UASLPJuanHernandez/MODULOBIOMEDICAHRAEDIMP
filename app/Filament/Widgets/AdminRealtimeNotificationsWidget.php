<?php

namespace App\Filament\Widgets;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Filament\Widgets\Widget;

class AdminRealtimeNotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.admin-realtime-notifications';
    protected static ?int $sort = 2;

    public ?int $unreadCount = null;
    public array $latest = [];

    public function mount(): void
    {
        $this->loadData();
    }

    // Permitir invocación desde JS (Echo) usando Livewire
    public function loadData(): void
    {
        // Evitar excepción si la migración aún no se ha ejecutado
        if (! Schema::hasTable('admin_notifications')) {
            $this->latest = [];
            $this->unreadCount = 0;
            return;
        }

        try {
            $query = AdminNotification::query()->latest();
            $this->latest = $query->limit(10)->get()->map(fn($n)=>([
                'id'=>$n->id,
                'title'=>$n->title,
                'message'=>$n->message,
                'action'=>$n->action,
                'read'=>$n->read,
                'time'=>$n->created_at->diffForHumans(),
            ]))->toArray();
            $this->unreadCount = AdminNotification::where('read', false)->count();
        } catch (\Throwable $e) {
            // En caso de cualquier problema, no romper el dashboard
            $this->latest = [];
            $this->unreadCount = 0;
        }
    }

    public function markAsRead(int $id): void
    {
        if (! Schema::hasTable('admin_notifications')) { return; }
        try {
            $n = AdminNotification::find($id);
            if($n && !$n->read) { $n->update(['read'=>true,'read_at'=>now()]); }
        } catch (\Throwable $e) {}
        $this->loadData();
    }

    public function markAllAsRead(): void
    {
        if (! Schema::hasTable('admin_notifications')) { return; }
        try {
            AdminNotification::where('read', false)->update(['read' => true, 'read_at' => now()]);
        } catch (\Throwable $e) {}
        $this->loadData();
    }

    public static function canView(): bool
    {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
        return $user && method_exists($user,'hasRole') && $user->hasRole('Administrador');
    }
}
