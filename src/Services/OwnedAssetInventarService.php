<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppAssets\Models\Asset;
use Illuminate\Support\Collection;

class OwnedAssetInventarService
{
    public function __construct(
        private readonly ItexiaAssetLookupService $itexiaAssetLookup,
    ) {}

    /**
     * @return Collection<int, array{id: int, label: string, itexia_id: ?string, serial_number: ?string}>
     */
    public function optionsForUser(User $user): Collection
    {
        return Asset::query()
            ->with(['vendor', 'type'])
            ->where('user_id', $user->id)
            ->orderBy('model')
            ->orderBy('name')
            ->get()
            ->map(fn (Asset $asset): array => [
                'id' => (int) $asset->id,
                'label' => $this->optionLabel($asset),
                'itexia_id' => filled($asset->itexia_id) ? (string) $asset->itexia_id : null,
                'serial_number' => filled($asset->serial_number) ? (string) $asset->serial_number : null,
            ])
            ->values();
    }

    /**
     * @return array{ok: bool, message?: string, data?: array<string, mixed>}
     */
    public function resolveSelection(User $user, int $assetId): array
    {
        $asset = Asset::query()
            ->with(['vendor', 'type', 'owner'])
            ->where('user_id', $user->id)
            ->whereKey($assetId)
            ->first();

        if ($asset === null) {
            return [
                'ok' => false,
                'message' => 'Das gewählte Asset gehört nicht zu Ihren Assets.',
            ];
        }

        $itexiaId = trim((string) ($asset->itexia_id ?? ''));
        if ($itexiaId !== '') {
            $lookup = $this->itexiaAssetLookup->findByBarcode($itexiaId);
            if ($lookup['found']) {
                return [
                    'ok' => true,
                    'data' => $lookup['data'],
                ];
            }
        }

        return [
            'ok' => true,
            'data' => $this->mapManualFromAsset($asset, $itexiaId !== '' ? $itexiaId : null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapManualFromAsset(Asset $asset, ?string $barcode = null): array
    {
        $raumIstId = $asset->itexia_actual_room_id !== null && (int) $asset->itexia_actual_room_id > 0
            ? (int) $asset->itexia_actual_room_id
            : null;

        $raumIstText = null;
        if ($raumIstId === null) {
            $location = trim((string) ($asset->location ?? ''));
            if ($location !== '') {
                $raumIstText = $location;
            } else {
                $ownerRaum = trim((string) ($asset->owner?->raum ?? ''));
                $raumIstText = $ownerRaum !== '' ? $ownerRaum : null;
            }
        }

        return [
            'beschreibung' => trim($asset->display_name) !== '' ? $asset->display_name : null,
            'barcode' => $barcode,
            'sn' => filled($asset->serial_number) ? (string) $asset->serial_number : null,
            'raum_ist_id' => $raumIstId,
            'raum_ist_text' => $raumIstText,
        ];
    }

    private function optionLabel(Asset $asset): string
    {
        $parts = array_filter([
            trim($asset->display_name),
            filled($asset->serial_number) ? 'SN: '.$asset->serial_number : null,
            filled($asset->itexia_id) ? 'Itexia: '.$asset->itexia_id : null,
        ]);

        return $parts !== [] ? implode(' · ', $parts) : 'Asset #'.$asset->id;
    }
}
