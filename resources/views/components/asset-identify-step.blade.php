<flux:card>
    <flux:heading size="lg">Asset identifizieren</flux:heading>

    <div class="mt-4">
        <flux:tab.group>
            <flux:tabs wire:model="identifyMode">
                <flux:tab name="itexia" icon="magnifying-glass">Itexia-Suche</flux:tab>
                <flux:tab name="owned" icon="computer-desktop">Aus meinen Assets</flux:tab>
                <flux:tab name="manual" icon="pencil-square">Manuelle Eingabe</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="itexia" class="mt-4 space-y-4">
                <flux:field>
                    <flux:label>Itexia-ID / Barcode</flux:label>
                    <flux:input wire:model="lookupBarcode" wire:keydown.enter.prevent="lookupItexia" placeholder="Barcode eingeben" />
                    <flux:error name="lookupBarcode" />
                </flux:field>
                <flux:button wire:click="lookupItexia" variant="primary">Itexia-Suche</flux:button>
            </flux:tab.panel>

            <flux:tab.panel name="owned" class="mt-4 space-y-4">
                @if($this->ownedAssetOptions->isEmpty())
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>Keine Assets gefunden</flux:callout.heading>
                        <flux:callout.text>
                            Ihnen sind derzeit keine Assets in der Assets-App zugewiesen. Nutzen Sie die Itexia-Suche oder manuelle Eingabe.
                        </flux:callout.text>
                    </flux:callout>
                @else
                    <flux:field>
                        <flux:label>Mein Asset</flux:label>
                        <flux:select
                            wire:model="ownedAssetId"
                            variant="listbox"
                            searchable
                            placeholder="Asset auswählen…"
                        >
                            @foreach($this->ownedAssetOptions as $option)
                                <flux:select.option :value="$option['id']">{{ $option['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="ownedAssetId" />
                        <flux:description>
                            Mit Itexia-ID wird automatisch die Itexia-Suche verwendet, sonst werden die Asset-Daten manuell übernommen.
                        </flux:description>
                    </flux:field>
                    <flux:button wire:click="applyOwnedAsset" variant="primary">Asset übernehmen</flux:button>
                @endif
            </flux:tab.panel>

            <flux:tab.panel name="manual" class="mt-4 space-y-4">
                <flux:text>Asset-Daten im nächsten Schritt manuell erfassen.</flux:text>
                <flux:button wire:click="startManuell" variant="primary">Weiter zur manuellen Eingabe</flux:button>
            </flux:tab.panel>
        </flux:tab.group>
    </div>
</flux:card>
