@php
    use Hwkdo\IntranetAppInventar\Support\SeventhingsSyncStatusPresenter;
    use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
    $presenter = app(SeventhingsSyncStatusPresenter::class);
@endphp

@component('mail::panel')
## Itexia / Seventhings

@foreach($syncSummary as $line)
**{{ $presenter->assetLabel($line->inventar) }}**

{{ $presenter->headline($line) }}

@if($detail = $presenter->detail($line))
{{ $detail }}
@endif

---
@endforeach
@endcomponent
