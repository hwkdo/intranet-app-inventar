<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - Meine Einstellungen')] class extends Component
{
};
?>

<div>
<x-intranet-app-inventar::inventar-layout heading="Meine Einstellungen" subheading="Persönliche Einstellungen für die Inventar App">
    @livewire('intranet-app-base::user-settings', ['appIdentifier' => 'inventar'])
</x-intranet-app-inventar::inventar-layout>
</div>
