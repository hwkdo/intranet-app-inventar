@props([
    'heading' => '',
    'subheading' => '',
    'navItems' => []
])

@php
    $defaultNavItems = [
        ['label' => 'Übersicht', 'href' => route('apps.inventar.index'), 'icon' => 'home', 'description' => 'Startseite der Inventar-App', 'buttonText' => 'Übersicht anzeigen'],
        ['label' => 'Anlagenabgang', 'href' => route('apps.inventar.aussonderung'), 'icon' => 'archive-box', 'description' => 'Schaden, Verlust, Verkauf oder Entsorgung melden', 'buttonText' => 'Anlagenabgang öffnen'],
        ['label' => 'Standortwechsel', 'href' => route('apps.inventar.standortwechsel'), 'icon' => 'arrow-right-circle', 'description' => 'Dauerhaften Raumwechsel melden und in Itexia umsetzen', 'buttonText' => 'Standortwechsel öffnen'],
        ['label' => 'Meine Einstellungen', 'href' => route('apps.inventar.settings.user'), 'icon' => 'cog-6-tooth', 'description' => 'Persönliche Einstellungen', 'buttonText' => 'Einstellungen öffnen', 'welcomeSection' => 'settings'],
        ['label' => 'App-Info', 'href' => route('apps.inventar.info'), 'icon' => 'information-circle', 'description' => 'Version und Release-Historie', 'buttonText' => 'App-Info anzeigen', 'welcomeSection' => 'settings'],
        ['label' => 'Admin', 'href' => route('apps.inventar.admin.index'), 'icon' => 'shield-check', 'description' => 'Einstellungen und Meldungen verwalten', 'buttonText' => 'Admin öffnen', 'permission' => 'manage-app-inventar', 'welcomeSection' => 'settings'],
    ];

    $navItems = ! empty($navItems) ? $navItems : $defaultNavItems;
    $customBgUrl = \Hwkdo\IntranetAppBase\Models\AppBackground::getCustomBackgroundUrl('inventar');
@endphp

@if($customBgUrl)
    @push('app-styles')
    <style data-app-bg data-ts="{{ uniqid() }}">
        :root { --app-bg-image: url('{{ $customBgUrl }}'); }
    </style>
    @endpush
@endif

@if(request()->routeIs('apps.inventar.index'))
    <x-intranet-app-base::app-layout
        app-identifier="inventar"
        :heading="$heading"
        :subheading="$subheading"
        :nav-items="$navItems"
        :wrap-in-card="false"
    >
        <x-intranet-app-base::app-index-auto
            app-identifier="inventar"
            app-name="Inventar"
            app-description="Anlagenabgang und dauerhafte Standortwechsel für Anlagegüter melden."
            :nav-items="$navItems"
            welcome-title="Willkommen bei Inventar"
            welcome-description="Melden Sie Anlagenabgänge oder dauerhafte Standortwechsel. Bei Standortwechseln wird Itexia/Seventhings direkt aktualisiert, sofern ein Barcode vorliegt. Anlagenabgänge können optional in Seventhings archiviert werden (Admin-Einstellung)."
        />
    </x-intranet-app-base::app-layout>
@else
    <x-intranet-app-base::app-layout
        app-identifier="inventar"
        :heading="$heading"
        :subheading="$subheading"
        :nav-items="$navItems"
        :wrap-in-card="true"
    >
        {{ $slot }}
    </x-intranet-app-base::app-layout>
@endif
