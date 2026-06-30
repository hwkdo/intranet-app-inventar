<?php

use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\ItexiaRoomListService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function meldungen()
    {
        return Meldung::query()
            ->with('user')
            ->latest()
            ->limit(200)
            ->get();
    }

    public function mailPreview(int $meldungId): string
    {
        $meldung = Meldung::query()->with('user')->findOrFail($meldungId);
        $roomList = app(ItexiaRoomListService::class);

        $raumIstId = $meldung->inventar['raum_ist_id'] ?? null;
        $raumIstLabel = is_numeric($raumIstId)
            ? $roomList->labelForId((int) $raumIstId)
            : ($meldung->inventar['raum_ist_text'] ?? null);

        $raumSollId = $meldung->data['raum_soll_id'] ?? null;
        $raumSollLabel = is_numeric($raumSollId) ? $roomList->labelForId((int) $raumSollId) : null;

        return (new InventarMeldungMail($meldung, [], $raumIstLabel, $raumSollLabel))->render();
    }
};
?>

<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>ID</flux:table.column>
            <flux:table.column>Datum</flux:table.column>
            <flux:table.column>Typ</flux:table.column>
            <flux:table.column>Melder</flux:table.column>
            <flux:table.column>Beschreibung</flux:table.column>
            <flux:table.column>Seventhings</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->meldungen as $meldung)
                <flux:table.row wire:key="m-{{ $meldung->id }}">
                    <flux:table.cell>{{ $meldung->id }}</flux:table.cell>
                    <flux:table.cell>{{ $meldung->created_at?->format('d.m.Y H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst($meldung->typ->value) }}</flux:table.cell>
                    <flux:table.cell>{{ $meldung->user?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $meldung->inventar['beschreibung'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if($meldung->seventhings_status)
                            <flux:badge size="sm">{{ $meldung->seventhings_status->value }}</flux:badge>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">Keine Meldungen vorhanden.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
