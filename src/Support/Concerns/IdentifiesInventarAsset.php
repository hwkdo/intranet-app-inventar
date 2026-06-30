<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Support\Concerns;

use App\Models\User;
use Hwkdo\IntranetAppInventar\Services\ItexiaAssetLookupService;
use Hwkdo\IntranetAppInventar\Services\ItexiaRoomListService;
use Hwkdo\IntranetAppInventar\Services\OwnedAssetInventarService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

trait IdentifiesInventarAsset
{
    public string $identifyMode = 'itexia';

    public string $lookupBarcode = '';

    public ?int $ownedAssetId = null;

    public int $step = 1;

    public bool $assetDatenReadonly = false;

    /** @var array<string, mixed> */
    public array $inventar = [
        'beschreibung' => null,
        'barcode' => null,
        'datev_nr' => null,
        'sn' => null,
        'lieferdatum' => null,
        'preis' => null,
        'raum_ist_id' => null,
        'raum_ist_text' => null,
    ];

    /** @return Collection<int, array{id: int, label: string, itexia_id: ?string, serial_number: ?string}> */
    #[Computed]
    public function ownedAssetOptions(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        return app(OwnedAssetInventarService::class)->optionsForUser($user);
    }

    #[Computed]
    public function inventarRaumIstLabel(): ?string
    {
        if (filled($this->inventar['raum_ist_text'] ?? null)) {
            return (string) $this->inventar['raum_ist_text'];
        }

        if (empty($this->inventar['raum_ist_id'])) {
            return null;
        }

        foreach (app(ItexiaRoomListService::class)->all() as $raum) {
            if ($raum['id'] === (int) $this->inventar['raum_ist_id']) {
                return $raum['label'];
            }
        }

        return null;
    }

    public function lookupItexia(): void
    {
        $result = app(ItexiaAssetLookupService::class)->findByBarcode($this->lookupBarcode);
        if (! $result['found']) {
            $this->addError('lookupBarcode', 'Kein Asset mit dieser Itexia-ID gefunden.');

            return;
        }

        $this->inventar = array_merge($this->emptyInventar(), $result['data']);
        $this->assetDatenReadonly = true;
        $this->step = 2;
        $this->resetErrorBag();
    }

    public function startManuell(): void
    {
        $this->inventar = $this->emptyInventar();
        $this->assetDatenReadonly = false;
        $this->step = 2;
        $this->resetErrorBag();
    }

    public function applyOwnedAsset(): void
    {
        $this->validate([
            'ownedAssetId' => ['required', 'integer', 'min:1'],
        ], [], [
            'ownedAssetId' => 'Asset',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $result = app(OwnedAssetInventarService::class)->resolveSelection($user, (int) $this->ownedAssetId);
        if (! $result['ok']) {
            $this->addError('ownedAssetId', $result['message'] ?? 'Asset konnte nicht übernommen werden.');

            return;
        }

        $this->inventar = array_merge($this->emptyInventar(), $result['data']);
        $this->assetDatenReadonly = true;
        $this->step = 2;
        $this->resetErrorBag();
    }

    /**
     * @return array<string, null>
     */
    protected function emptyInventar(): array
    {
        return [
            'beschreibung' => null,
            'barcode' => null,
            'datev_nr' => null,
            'sn' => null,
            'lieferdatum' => null,
            'preis' => null,
            'raum_ist_id' => null,
            'raum_ist_text' => null,
        ];
    }
}
