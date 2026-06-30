<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - Beispiel')] class extends Component
{
    /** @var array{name: string, description: string, status: string, created_at: string} */
    public array $exampleData = [
        'name' => 'Beispiel Item',
        'description' => 'Dies ist ein Beispiel-Item für die Inventar App',
        'status' => 'active',
        'created_at' => '',
    ];

    public function mount(): void
    {
        $this->exampleData['created_at'] = now()->format('d.m.Y H:i');
    }
};
?>

<div>
<x-intranet-app-inventar::inventar-layout heading="Beispiel-Seite" subheading="Demonstration der Inventar-Funktionalität">
    <flux:card>
        <flux:heading size="lg" class="mb-4">Beispiel-Content</flux:heading>
        <flux:text class="mb-6">
            Diese Seite zeigt, wie eine typische App-Seite aussehen könnte.
        </flux:text>

        <div class="space-y-4">
            <div class="rounded-lg border p-4">
                <flux:heading size="md">{{ $exampleData['name'] }}</flux:heading>
                <flux:text class="mt-2">{{ $exampleData['description'] }}</flux:text>
                <div class="mt-2 flex items-center gap-2">
                    <flux:badge variant="success">{{ $exampleData['status'] }}</flux:badge>
                    <flux:text class="text-sm text-zinc-500">{{ $exampleData['created_at'] }}</flux:text>
                </div>
            </div>

            <div class="rounded-lg border p-4">
                <flux:heading size="md">Weitere Informationen</flux:heading>
                <flux:text class="mt-2">
                    Hier könnten weitere Details oder Aktionen für das Beispiel-Item angezeigt werden.
                </flux:text>
                <div class="mt-4 flex gap-2">
                    <flux:button variant="primary" size="sm">Bearbeiten</flux:button>
                    <flux:button variant="outline" size="sm">Löschen</flux:button>
                </div>
            </div>
        </div>
    </flux:card>
</x-intranet-app-inventar::inventar-layout>
</div>
