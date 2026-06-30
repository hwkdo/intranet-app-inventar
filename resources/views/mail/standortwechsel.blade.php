@component('mail::message')

@include('intranet-app-inventar::mail.partials.absender', ['meldung' => $meldung])
@include('intranet-app-inventar::mail.partials.inventardaten', ['meldung' => $meldung])

@if(!empty($syncSummary))
@include('intranet-app-inventar::mail.partials.seventhings-sync-status', ['syncSummary' => $syncSummary])
@endif

@component('mail::panel')
@if(filled($raumIstLabel))
**Aktueller Raum:** {{ $raumIstLabel }}
@elseif(!empty($meldung->inventar['raum_ist_text']))
**Aktueller Raum:** {{ $meldung->inventar['raum_ist_text'] }}
@endif

@if(filled($raumSollLabel))
**Neuer Raum:** {{ $raumSollLabel }}
@elseif(!empty($meldung->data['raum_soll_text']))
**Neuer Raum:** {{ $meldung->data['raum_soll_text'] }}
@endif
@endcomponent

@endcomponent
