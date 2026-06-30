<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - App-Info')] class extends Component
{
};
?>

<div>
<x-intranet-app-inventar::inventar-layout heading="App-Info" subheading="Installierte Version und Release-Historie">
    @livewire('intranet-app-base::app-info', ['appIdentifier' => 'inventar'])
</x-intranet-app-inventar::inventar-layout>
</div>
