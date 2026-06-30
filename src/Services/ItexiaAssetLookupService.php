<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use Hwkdo\SeventhingsLaravel\Models\Asset as ItexiaAsset;
use Hwkdo\SeventhingsLaravel\SeventhingsLaravel;
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
        $raumIst = $asset->raum_ist ?? null;
        $raumIstId = null;
        if (is_object($raumIst) && isset($raumIst->id)) {
            $raumIstId = (int) $raumIst->id;
        } elseif (is_numeric($raumIst)) {
            $raumIstId = (int) $raumIst;
        }

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
