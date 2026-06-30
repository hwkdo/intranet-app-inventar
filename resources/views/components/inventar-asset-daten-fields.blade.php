@props([
    'raumLabel' => 'Aktueller Raum',
])

<div {{ $attributes->merge(['class' => 'mt-4 grid gap-4 md:grid-cols-2']) }}>
    @if(filled($this->inventar['barcode'] ?? null))
        <flux:input wire:model="inventar.barcode" label="Barcode" readonly />
        <flux:input wire:model="inventar.datev_nr" label="Datev-ID" readonly />
    @endif

    @if($this->assetDatenReadonly)
        <flux:input wire:model="inventar.beschreibung" label="Beschreibung" readonly />
        <flux:input wire:model="inventar.sn" label="Seriennummer" readonly />
        <flux:input label="{{ $raumLabel }}" value="{{ $this->inventarRaumIstLabel ?? '—' }}" readonly />
        @if(filled($this->inventar['lieferdatum'] ?? null))
            <flux:input wire:model="inventar.lieferdatum" label="Lieferdatum" readonly />
        @endif
        @if(filled($this->inventar['preis'] ?? null))
            <flux:input wire:model="inventar.preis" label="Historischer Anschaffungspreis" description="Euro" readonly />
        @endif
    @else
        <flux:input wire:model="inventar.beschreibung" label="Beschreibung" required />
        <flux:input wire:model="inventar.sn" label="Seriennummer" />
        <flux:select wire:model="inventar.raum_ist_id" label="{{ $raumLabel }}" placeholder="Raum wählen">
            @foreach($this->raeume as $raum)
                <flux:select.option :value="$raum['id']">{{ $raum['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input wire:model="inventar.raum_ist_text" label="Raum (Freitext, falls nicht gelistet)" />
    @endif
</div>
