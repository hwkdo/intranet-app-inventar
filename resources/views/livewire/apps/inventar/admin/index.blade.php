<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Inventar - Admin')] class extends Component
{
    public string $activeTab = 'einstellungen';

    public function mount(): void
    {
        $tab = request()->query('tab');

        if (is_string($tab) && in_array($tab, ['einstellungen', 'hintergrundbild', 'meldungen'], true)) {
            $this->activeTab = $tab;
        }
    }
};
?>

<div>
    <x-intranet-app-inventar::inventar-layout heading="Inventar" subheading="Administration">
        <flux:tab.group>
            <flux:tabs wire:model.live="activeTab">
                <flux:tab name="einstellungen" icon="cog-6-tooth">Einstellungen</flux:tab>
                <flux:tab name="hintergrundbild" icon="photo">Hintergrundbild</flux:tab>
                <flux:tab name="meldungen" icon="clipboard-document-list">Meldungen</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="einstellungen">
                @if ($activeTab === 'einstellungen')
                    <div class="min-h-[400px]">
                        @livewire('intranet-app-base::admin-settings', [
                            'appIdentifier' => 'inventar',
                            'settingsModelClass' => '\Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings',
                            'appSettingsClass' => '\Hwkdo\IntranetAppInventar\Data\AppSettings',
                        ], key('inventar-admin-settings'))
                    </div>
                @endif
            </flux:tab.panel>

            <flux:tab.panel name="hintergrundbild">
                @if ($activeTab === 'hintergrundbild')
                    <div class="min-h-[400px]">
                        @livewire('intranet-app-base::app-background-image', [
                            'appIdentifier' => 'inventar',
                        ], key('inventar-admin-background'))
                    </div>
                @endif
            </flux:tab.panel>

            <flux:tab.panel name="meldungen">
                @if ($activeTab === 'meldungen')
                    <div class="min-h-[400px]">
                        @livewire('intranet-app-inventar::apps.inventar.admin.meldungen', key('inventar-admin-meldungen'))
                    </div>
                @endif
            </flux:tab.panel>
        </flux:tab.group>
    </x-intranet-app-inventar::inventar-layout>
</div>
