<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold">Notificaciones</h2>
            <span class="text-xs {{ $unreadCount ? 'text-danger-600' : 'text-gray-500' }}">{{ $unreadCount }} sin leer</span>
        </div>
        <ul class="space-y-2 max-h-72 overflow-y-auto" wire:poll.10s="loadData">
            @forelse($latest as $n)
                <li class="p-2 rounded border text-xs {{ $n['read'] ? 'bg-white' : 'bg-warning-50 border-warning-200' }}">
                    <div class="font-medium">{{ $n['title'] }}</div>
                    <div class="text-gray-600">{{ $n['message'] }}</div>
                    <div class="flex justify-between mt-1 text-[11px] text-gray-500">
                        <span>{{ $n['time'] }}</span>
                        @if(!$n['read'])
                            <button wire:click="markAsRead({{ $n['id'] }})" class="text-primary-600 hover:underline">Marcar leído</button>
                        @endif
                    </div>
                </li>
            @empty
                <li class="text-xs text-gray-500">Sin notificaciones aún.</li>
            @endforelse
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
