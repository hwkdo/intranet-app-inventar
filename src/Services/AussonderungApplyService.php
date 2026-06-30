<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppAssets\Models\Asset;
use Hwkdo\IntranetAppAssets\Services\AssetDisposalFromInventarService;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\SeventhingsLaravel\Services\ItexiaAssetArchiveService;

class AussonderungApplyService
{
    public function __construct(
        private readonly AssetDisposalFromInventarService $assetDisposalService,
        private readonly ItexiaAssetArchiveService $archiveService,
        private readonly MeldungInventurhinweisBuilder $inventurhinweisBuilder,
    ) {}

    /**
     * @return array{status: SeventhingsSyncStatus, errorMessage: ?string}
     */
    public function apply(Meldung $meldung, User $actor): array
    {
        $settings = IntranetAppInventarSettings::current()?->settings;
        $archiveEnabled = $settings?->seventhingsArchivierenBeiAnlagenabgang ?? false;

        $barcode = trim((string) ($meldung->inventar['barcode'] ?? ''));
        $localAsset = $barcode !== ''
            ? Asset::query()->with(['type', 'vendor'])->where('itexia_id', $barcode)->first()
            : null;

        if (! $archiveEnabled) {
            $this->persistSeventhingsStatus(
                $meldung,
                SeventhingsSyncStatus::Skipped,
                'Automatische Archivierung ist in den Administrator-Einstellungen deaktiviert.',
            );
            $this->disposeLocalAssetIfPresent($meldung, $actor, $localAsset);

            return [
                'status' => SeventhingsSyncStatus::Skipped,
                'errorMessage' => 'Automatische Archivierung ist in den Administrator-Einstellungen deaktiviert.',
            ];
        }

        if ($barcode === '') {
            $this->persistSeventhingsStatus(
                $meldung,
                SeventhingsSyncStatus::Skipped,
                'Kein Barcode angegeben — automatische Archivierung nicht möglich.',
            );

            return [
                'status' => SeventhingsSyncStatus::Skipped,
                'errorMessage' => 'Kein Barcode angegeben — automatische Archivierung nicht möglich.',
            ];
        }

        $objectUuid = filled($localAsset?->itexia_uuid) ? trim((string) $localAsset->itexia_uuid) : null;
        $archiveResult = $this->archiveService->archiveByBarcode(
            $objectUuid,
            $barcode,
            $this->inventurhinweisBuilder->forAnlagenabgang($meldung, $actor),
            $settings?->inventurhinweisAnhaengen ?? true,
        );

        if ($archiveResult->success) {
            $this->persistSeventhingsStatus($meldung, SeventhingsSyncStatus::Applied);
        } else {
            $this->persistSeventhingsStatus(
                $meldung,
                SeventhingsSyncStatus::Failed,
                $archiveResult->errorMessage,
            );
        }

        $this->disposeLocalAssetIfPresent($meldung, $actor, $localAsset);

        return [
            'status' => $archiveResult->success ? SeventhingsSyncStatus::Applied : SeventhingsSyncStatus::Failed,
            'errorMessage' => $archiveResult->success ? null : $archiveResult->errorMessage,
        ];
    }

    private function persistSeventhingsStatus(
        Meldung $meldung,
        SeventhingsSyncStatus $status,
        ?string $errorMessage = null,
    ): void {
        $meldung->update([
            'seventhings_status' => $status,
            'seventhings_error' => $errorMessage,
            'seventhings_applied_at' => $status === SeventhingsSyncStatus::Applied ? now() : null,
        ]);
    }

    private function disposeLocalAssetIfPresent(Meldung $meldung, User $actor, ?Asset $asset): void
    {
        if ($asset === null) {
            return;
        }

        $deleteReason = $this->buildDeleteReason($meldung);
        $noteText = sprintf(
            'Anlagenabgang gemeldet am %s durch %s: %s',
            now()->format('d.m.Y H:i'),
            $actor->name,
            $deleteReason,
        );

        $this->assetDisposalService->disposeFromInventarMeldung(
            $asset,
            $actor,
            $noteText,
            $deleteReason,
            [
                'type_name' => (string) ($asset->type?->name ?? ''),
                'vendor_name' => (string) ($asset->vendor?->name ?? ''),
                'model' => (string) ($asset->model ?? ''),
                'itexia_id' => $asset->itexia_id,
                'itexia_uuid' => $asset->itexia_uuid,
                'display_name' => $asset->display_name,
            ],
        );
    }

    private function buildDeleteReason(Meldung $meldung): string
    {
        $parts = ['Inventar-Anlagenabgang'];
        $beschreibung = $meldung->inventar['beschreibung'] ?? null;
        if (is_string($beschreibung) && $beschreibung !== '') {
            $parts[] = $beschreibung;
        }
        $art = $meldung->data['art_abgang'] ?? null;
        if (is_string($art) && $art !== '') {
            $parts[] = $art;
        }

        return implode(' — ', $parts);
    }
}
