<?php

use function Livewire\Volt\title;

title('Hintergrund');

?>

<div>
    <x-intranet-app-inventar::inventar-layout heading="Hintergrund" subheading="Geplante Tasks, Jobs, Mails und Benachrichtigungen">
        @livewire('intranet-app-base::background-activities', ['appIdentifier' => 'inventar'])
    </x-intranet-app-inventar::inventar-layout>
</div>
