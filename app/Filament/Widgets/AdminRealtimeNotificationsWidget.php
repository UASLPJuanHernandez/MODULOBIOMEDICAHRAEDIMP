<?php

namespace App\Filament\Widgets;

use App\Models\AdminNotification;
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

    protected function loadData(): void
    {
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
    }

    public function markAsRead(int $id): void
    {
        $n = AdminNotification::find($id);
        if($n && !$n->read) { $n->update(['read'=>true,'read_at'=>now()]); }
        $this->loadData();
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && method_exists($user,'hasRole') && $user->hasRole('Administrador');
    }
}
