<?php

use App\Models\User;
use Hwkdo\IntranetAppInventar\Services\ItexiaAssetLookupService;
use Hwkdo\IntranetAppInventar\Services\ItexiaRoomListService;
use Hwkdo\IntranetAppInventar\Services\MeldungSubmissionService;
use Hwkdo\IntranetAppInventar\Support\Concerns\IdentifiesInventarAsset;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - Anlagenabgang')] class extends Component
{
    use IdentifiesInventarAsset;

    public ?string $datum = null;

    public string $oeffentliche_mittel = '';

    public string $oeffentliche_mittel_bindungsfrist = '';

    public string $art_abgang = '';

    public string $grund1 = '';

    public string $grund2_schaden_in_kursnr = '';

    public string $grund2_schaden_kurs_ausbilder = '';

    public string $grund2_alter_des_gegenstands = '';

    public ?string $grund2_verkauf_datum = null;

    public string $grund2_verkauf_preis = '';

    public string $grund2_anderer_grund = '';

    public bool $massenAktiv = false;

    /** @var list<array{barcode: string, sn: string}> */
    public array $massenEintraege = [];

    public bool $submitted = false;

    #[Computed]
    public function raeume(): array
    {
        return app(ItexiaRoomListService::class)->all();
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
        $rules = [
            'inventar.beschreibung' => ['required', 'string', 'max:500'],
            'inventar.raum_ist_id' => ['nullable', 'integer'],
            'inventar.raum_ist_text' => ['nullable', 'string', 'max:100'],
            'datum' => ['required', 'date'],
            'oeffentliche_mittel' => ['required', 'in:Ja,Nein'],
            'art_abgang' => ['required', 'in:Verkauf,Verschrottung / Entsorgung,Diebstahl'],
        ];

        if ($this->oeffentliche_mittel === 'Ja') {
            $rules['oeffentliche_mittel_bindungsfrist'] = ['required', 'in:Ja,Nein'];
        }

        if ($this->art_abgang === 'Verschrottung / Entsorgung') {
            $rules['grund1'] = ['required', 'string'];
        }

        $this->validate($rules);

        if (empty($this->inventar['raum_ist_id']) && empty($this->inventar['raum_ist_text'])) {
            $this->addError('inventar.raum_ist_id', 'Bitte Standort angeben.');

            return;
        }

        $data = array_filter([
            'datum' => $this->datum,
            'oeffentliche_mittel' => $this->oeffentliche_mittel,
            'oeffentliche_mittel_bindungsfrist' => $this->oeffentliche_mittel_bindungsfrist ?: null,
            'art_abgang' => $this->art_abgang,
            'grund1' => $this->grund1 ?: null,
            'grund2_schaden_in_kursnr' => $this->grund2_schaden_in_kursnr ?: null,
            'grund2_schaden_kurs_ausbilder' => $this->grund2_schaden_kurs_ausbilder ?: null,
            'grund2_alter_des_gegenstands' => $this->grund2_alter_des_gegenstands ?: null,
            'grund2_verkauf_datum' => $this->grund2_verkauf_datum,
            'grund2_verkauf_preis' => $this->grund2_verkauf_preis ?: null,
            'grund2_anderer_grund' => $this->grund2_anderer_grund ?: null,
        ], fn ($v) => $v !== null && $v !== '');

        $entries = [['inventar' => $this->inventar, 'data' => $data]];

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
                    $inventar = $lookup['found'] ? array_merge($inventar, $lookup['data']) : array_merge($inventar, ['barcode' => $barcode]);
                }
                if ($sn !== '') {
                    $inventar['sn'] = $sn;
                }

                $entries[] = ['inventar' => $inventar, 'data' => $data];
            }
        }

        /** @var User $user */
        $user = auth()->user();
        app(MeldungSubmissionService::class)->submitAussonderung($user, $entries);

        $this->submitted = true;
        session()->flash('success', 'Anlagenabgang wurde gemeldet.');
    }
};
?>

<div>
<x-intranet-app-inventar::inventar-layout heading="Anlagenabgang" subheading="Schaden, Verlust, Verkauf oder Entsorgung von Anlagegütern">
    <div class="space-y-6 max-w-4xl">
        @if($this->submitted)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>Meldung gesendet</flux:callout.heading>
                <flux:callout.text>Der Anlagenabgang wurde erfasst und die zuständigen Personen informiert.</flux:callout.text>
            </flux:callout>
            <flux:button href="{{ route('apps.inventar.index') }}" variant="primary">Zur Übersicht</flux:button>
        @else
            @if($this->step === 1)
                <x-intranet-app-inventar::asset-identify-step />
            @else
                <flux:card>
                    <flux:heading size="lg">Asset-Daten</flux:heading>
                    <x-intranet-app-inventar::inventar-asset-daten-fields raum-label="Standort (Raum)" />
                    <flux:error name="inventar.raum_ist_id" />
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">Anlagenabgang</flux:heading>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <flux:input wire:model="datum" type="date" label="Festgestellt am" required />
                        <flux:select wire:model.live="oeffentliche_mittel" label="Öffentliche Mittel?" required>
                            <flux:select.option value="">Bitte wählen</flux:select.option>
                            <flux:select.option value="Ja">Ja</flux:select.option>
                            <flux:select.option value="Nein">Nein</flux:select.option>
                        </flux:select>
                        @if($this->oeffentliche_mittel === 'Ja')
                            <flux:select wire:model="oeffentliche_mittel_bindungsfrist" label="Bindungsfrist abgelaufen?" required>
                                <flux:select.option value="">Bitte wählen</flux:select.option>
                                <flux:select.option value="Ja">Ja</flux:select.option>
                                <flux:select.option value="Nein">Nein</flux:select.option>
                            </flux:select>
                        @endif
                        <flux:select wire:model.live="art_abgang" label="Art des Anlagenabgangs" required>
                            <flux:select.option value="">Bitte wählen</flux:select.option>
                            <flux:select.option value="Verkauf">Verkauf</flux:select.option>
                            <flux:select.option value="Verschrottung / Entsorgung">Verschrottung / Entsorgung</flux:select.option>
                            <flux:select.option value="Diebstahl">Diebstahl</flux:select.option>
                        </flux:select>
                    </div>

                    @if($this->art_abgang === 'Diebstahl')
                        <flux:callout variant="danger" class="mt-4" icon="exclamation-triangle">
                            Ein Diebstahl ist unverzüglich der Polizei sowie der Versicherung zu melden.
                        </flux:callout>
                    @endif

                    @if($this->art_abgang === 'Verschrottung / Entsorgung')
                        <div class="mt-4">
                            <flux:select wire:model.live="grund1" label="Grund" required>
                                <flux:select.option value="">Bitte wählen</flux:select.option>
                                <flux:select.option value="grund_ist_bedienerfehler_mitarbeiter_hwk">Schaden - Bedienerfehler Mitarbeiter HWK</flux:select.option>
                                <flux:select.option value="grund_ist_bedienerfehler_dritte">Schaden - Bedienerfehler Dritte</flux:select.option>
                                <flux:select.option value="grund_ist_unachtsamkeit_mitarbeiter_hwk">Schaden - Unachtsamkeit Mitarbeiter HWK</flux:select.option>
                                <flux:select.option value="grund_ist_aeussere_einfluesse">Schaden - Äußere Einflüsse</flux:select.option>
                                <flux:select.option value="grund_ist_abnutzung">Abnutzung</flux:select.option>
                                <flux:select.option value="grund_ist_anderer_grund">Anderer Grund</flux:select.option>
                            </flux:select>
                            @if(in_array($this->grund1, ['grund_ist_bedienerfehler_dritte', 'grund_ist_unachtsamkeit_dritte'], true))
                                <div class="grid gap-4 md:grid-cols-2 mt-4">
                                    <flux:input wire:model="grund2_schaden_in_kursnr" label="Kursnr." />
                                    <flux:input wire:model="grund2_schaden_kurs_ausbilder" label="Ausbilder" />
                                </div>
                            @endif
                            @if($this->grund1 === 'grund_ist_abnutzung')
                                <flux:input wire:model="grund2_alter_des_gegenstands" label="Alter des Gegenstands (Jahre)" class="mt-4" />
                            @endif
                            @if($this->grund1 === 'grund_ist_anderer_grund')
                                <flux:input wire:model="grund2_anderer_grund" label="Beschreibung" class="mt-4" />
                            @endif
                        </div>
                    @endif
                </flux:card>

                <flux:card>
                    <flux:checkbox wire:model.live="massenAktiv" label="Massenverarbeitung aktivieren" />
                    @if($this->massenAktiv)
                        <div class="mt-4 space-y-3">
                            @foreach($this->massenEintraege as $index => $eintrag)
                                <div class="flex gap-2 items-end" wire:key="mass-a-{{ $index }}">
                                    <flux:input wire:model="massenEintraege.{{ $index }}.barcode" label="Barcode" class="flex-1" />
                                    <flux:input wire:model="massenEintraege.{{ $index }}.sn" label="SN" class="flex-1" />
                                    <flux:button wire:click="removeMassenEintrag({{ $index }})" variant="danger" size="sm" icon="minus" />
                                </div>
                            @endforeach
                            <flux:button wire:click="addMassenEintrag" variant="ghost" icon="plus">Weiteres Asset</flux:button>
                        </div>
                    @endif
                </flux:card>

                <flux:button wire:click="submit" variant="primary" class="w-full">Anlagenabgang melden</flux:button>
            @endif
        @endif
    </div>
</x-intranet-app-inventar::inventar-layout>
</div>
