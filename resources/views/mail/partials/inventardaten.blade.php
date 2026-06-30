@component('mail::panel')
**Beschreibung:** {{ $meldung->inventar['beschreibung'] ?? '—' }}

@if(!empty($meldung->inventar['barcode']))
**Barcode / Itexia-ID:** {{ $meldung->inventar['barcode'] }}
@endif

@if(!empty($meldung->inventar['sn']))
**Seriennummer:** {{ $meldung->inventar['sn'] }}
@endif

@if(!empty($meldung->inventar['datev_nr']))
**Datev-ID:** {{ $meldung->inventar['datev_nr'] }}
@endif

@if(!empty($meldung->inventar['lieferdatum']))
**Lieferdatum:** {{ $meldung->inventar['lieferdatum'] }}
@endif

@if(filled($meldung->inventar['preis'] ?? null))
**Historischer Anschaffungspreis:** {{ $meldung->inventar['preis'] }} Euro
@endif
@endcomponent
