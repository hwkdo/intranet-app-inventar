**Gemeldet von:** {{ $meldung->user?->name ?? 'Unbekannt' }} ({{ $meldung->user?->email ?? '—' }})  
**Datum der Meldung:** {{ $meldung->created_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}
