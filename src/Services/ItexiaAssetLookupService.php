<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use Hwkdo\SeventhingsLaravel\Models\Asset as ItexiaAsset;
use Hwkdo\SeventhingsLaravel\SeventhingsLaravel;
use Hwkdo\SeventhingsLaravel\Support\ItexiaRoomReferenceId;
use Throwable;

class ItexiaAssetLookupService
{
    public function __construct(
        private readonly SeventhingsLaravel $seventhings,
    ) {}

    /**
     * @return array{found: bool, data?: array<string, mixed>}
     */
    public function findByBarcode(string $barcode): array
    {
        $barcode = trim($barcode);
        if ($barcode === '') {
            return ['found' => false];
        }

        try {
            $asset = $this->seventhings->findAsset($barcode);
        } catch (Throwable) {
            return ['found' => false];
        }

        if (! $asset instanceof ItexiaAsset) {
            return ['found' => false];
        }

        return [
            'found' => true,
            'data' => $this->mapAsset($asset, $barcode),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAsset(ItexiaAsset $asset, string $barcode): array
    {
        // Kein $asset->raum_ist: der Accessor ruft findRaumById() auf und kann bei
        // unvollständigen Room-API-Antworten (fehlendes id) crashen. Nur Rohwert nutzen.
        $raumIstId = ItexiaRoomReferenceId::fromApiValue($asset->getRawData('actual_room'));

        return [
            'barcode' => $barcode,
            'datev_nr' => $asset->datev_nr ?? null,
            'sn' => $asset->sn ?? null,
            'beschreibung' => $asset->beschreibung ?? null,
            'lieferdatum' => $asset->lieferdatum ?? null,
            'preis' => $asset->preis ?? null,
            'raum_ist_id' => $raumIstId,
        ];
    }
}
