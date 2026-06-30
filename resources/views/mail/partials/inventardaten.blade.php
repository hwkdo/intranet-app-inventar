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
@endcomponent
