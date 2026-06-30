<?php

use App\Models\User;
use Hwkdo\IntranetAppInventar\Services\ItexiaAssetLookupService;
use Hwkdo\IntranetAppInventar\Services\ItexiaRoomListService;
use Hwkdo\IntranetAppInventar\Services\MeldungSubmissionService;
use Hwkdo\IntranetAppInventar\Support\Concerns\IdentifiesInventarAsset;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - Standortwechsel')] class extends Component
{
    use IdentifiesInventarAsset;

    public ?int $raum_soll_id = null;

    public bool $massenAktiv = false;

    /** @var list<array{barcode: string, sn: string}> */
    public array $massenEintraege = [];

    public bool $submitted = false;

    #[Computed]
    public function raeume(): array
    {
        return app(ItexiaRoomListService::class)->all();
    }

    #[Computed]
    public function raumIst(): ?array
    {
        if (empty($this->inventar['raum_ist_id'])) {
            return null;
        }

        foreach ($this->raeume as $raum) {
            if ($raum['id'] === (int) $this->inventar['raum_ist_id']) {
                return $raum;
            }
        }

        return null;
    }

    #[Computed]
    public function raumSoll(): ?array
    {
        if ($this->raum_soll_id === null) {
            return null;
        }

        foreach ($this->raeume as $raum) {
            if ($raum['id'] === $this->raum_soll_id) {
                return $raum;
            }
        }

        return null;
    }

    public function addMassenEintrag(): void
    {
        $this->massenEintraege[] = ['barcode' => '', 'sn' => ''];
    }

    public function removeMassenEintrag(int $index): void
    {
        unset($this->massenEintraege[$index]);
        $this->massenEintraege = array_values($this->massenEintraege);
    }

    public function submit(): void
    {
        $this->validate([
            'inventar.beschreibung' => ['required', 'string', 'max:500'],
            'inventar.raum_ist_id' => ['nullable', 'integer'],
            'inventar.raum_ist_text' => ['nullable', 'string', 'max:100'],
            'raum_soll_id' => ['required', 'integer', 'min:1'],
        ], [], [
            'inventar.beschreibung' => 'Beschreibung',
            'raum_soll_id' => 'Neuer Raum',
        ]);

        if (empty($this->inventar['raum_ist_id']) && empty($this->inventar['raum_ist_text'])) {
            $this->addError('inventar.raum_ist_id', 'Bitte aktuellen Standort angeben.');

            return;
        }

        $entries = [[
            'inventar' => $this->inventar,
            'data' => ['raum_soll_id' => $this->raum_soll_id],
        ]];

        if ($this->massenAktiv) {
            foreach ($this->massenEintraege as $eintrag) {
                $barcode = trim($eintrag['barcode']);
                $sn = trim($eintrag['sn']);
                if ($barcode === '' && $sn === '') {
                    continue;
                }

                $inventar = $this->inventar;
                if ($barcode !== '') {
                    $lookup = app(ItexiaAssetLookupService::class)->findByBarcode($barcode);
                    if ($lookup['found']) {
                        $inventar = array_merge($inventar, $lookup['data']);
                    } else {
                        $inventar['barcode'] = $barcode;
                    }
                }
                if ($sn !== '') {
                    $inventar['sn'] = $sn;
                }

                $entries[] = [
                    'inventar' => $inventar,
                    'data' => ['raum_soll_id' => $this->raum_soll_id],
                ];
            }
        }

        /** @var User $user */
        $user = auth()->user();
        app(MeldungSubmissionService::class)->submitStandortwechsel($user, $entries);

        $this->submitted = true;
        session()->flash('success', 'Standortwechsel wurde gemeldet. Betroffene Personen wurden per E-Mail informiert.');
    }
};
?>

<div>
<x-intranet-app-inventar::inventar-layout heading="Standortwechsel" subheading="Dauerhafter Standortwechsel (länger als 6 Monate)">
    <div class="space-y-6 max-w-4xl">
        @if($this->submitted)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>Meldung gesendet</flux:callout.heading>
                <flux:callout.text>Der Standortwechsel wurde erfasst. Die Benachrichtigung enthält den Seventhings-Sync-Status.</flux:callout.text>
            </flux:callout>
            <flux:button href="{{ route('apps.inventar.index') }}" variant="primary">Zur Übersicht</flux:button>
        @else
            @if($this->step === 1)
                <x-intranet-app-inventar::asset-identify-step />
            @else
                <flux:card>
                    <flux:heading size="lg">Asset-Daten</flux:heading>
                    <x-intranet-app-inventar::inventar-asset-daten-fields />
                    <flux:error name="inventar.raum_ist_id" />
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">Neuer Raum</flux:heading>
                    <div class="mt-4">
                        <flux:select
                            wire:model="raum_soll_id"
                            variant="listbox"
                            searchable
                            label="Neuer Raum (Pflicht)"
                            placeholder="Zielraum suchen…"
                            required
                        >
                            @foreach($this->raeume as $raum)
                                <flux:select.option :value="$raum['id']">{{ $raum['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="raum_soll_id" />
                        @if($this->raumSoll)
                            <p class="mt-2 text-sm text-zinc-500">{{ $this->raumSoll['name'] }} · {{ $this->raumSoll['gebaeude'] }}</p>
                        @endif
                    </div>
                </flux:card>

                <flux:card>
                    <flux:checkbox wire:model.live="massenAktiv" label="Massenverarbeitung aktivieren" />
                    @if($this->massenAktiv)
                        <div class="mt-4 space-y-3">
                            <flux:text>Zusätzliche Assets (gleicher Zielraum):</flux:text>
                            @foreach($this->massenEintraege as $index => $eintrag)
                                <div class="flex gap-2 items-end" wire:key="mass-{{ $index }}">
                                    <flux:input wire:model="massenEintraege.{{ $index }}.barcode" label="Barcode" class="flex-1" />
                                    <flux:input wire:model="massenEintraege.{{ $index }}.sn" label="SN" class="flex-1" />
                                    <flux:button wire:click="removeMassenEintrag({{ $index }})" variant="danger" size="sm" icon="minus" />
                                </div>
                            @endforeach
                            <flux:button wire:click="addMassenEintrag" variant="ghost" icon="plus">Weiteres Asset</flux:button>
                        </div>
                    @endif
                </flux:card>

                <flux:button wire:click="submit" variant="primary" class="w-full">Standortwechsel melden</flux:button>
            @endif
        @endif
    </div>
</x-intranet-app-inventar::inventar-layout>
</div>
