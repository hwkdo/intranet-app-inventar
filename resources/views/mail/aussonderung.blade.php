@component('mail::message')

@include('intranet-app-inventar::mail.partials.absender', ['meldung' => $meldung])
@include('intranet-app-inventar::mail.partials.inventardaten', ['meldung' => $meldung])

@if(!empty($syncSummary))
@include('intranet-app-inventar::mail.partials.seventhings-sync-status', ['syncSummary' => $syncSummary])
@endif

@component('mail::panel')
@foreach($meldung->data as $key => $value)
@if($key === 'grund1')
**{{ $meldung->getLabel($key) }}:** {{ $meldung->getGrundLabel((string) $value) }}
@elseif(str_contains((string) $key, 'datum') && filled($value))
**{{ $meldung->getLabel($key) }}:** {{ \Carbon\Carbon::parse($value)->format('d.m.Y') }}
@elseif(filled($value))
**{{ $meldung->getLabel($key) }}:** {{ is_array($value) ? json_encode($value) : $value }}
@endif
@endforeach
@endcomponent

@endcomponent
