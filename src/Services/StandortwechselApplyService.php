<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\SeventhingsLaravel\Services\ItexiaRoomUpdateService;

class StandortwechselApplyService
{
    public function __construct(
        private readonly ItexiaRoomUpdateService $roomUpdateService,
        private readonly ItexiaRoomListService $roomListService,
        private readonly MeldungInventurhinweisBuilder $inventurhinweisBuilder,
    ) {}

    public function apply(Meldung $meldung, int $targetRoomId, User $actor): SeventhingsSyncStatus
    {
        $barcode = trim((string) ($meldung->inventar['barcode'] ?? ''));

        if ($barcode === '') {
            $meldung->update([
                'seventhings_status' => SeventhingsSyncStatus::Skipped,
                'seventhings_error' => null,
            ]);

            return SeventhingsSyncStatus::Skipped;
        }

        $settings = IntranetAppInventarSettings::current()?->settings;
        $roomSollLabel = $this->roomLabel($targetRoomId);
        $roomIstLabel = $this->resolveRaumIstLabel($meldung);

        $result = $this->roomUpdateService->updateActualAndTargetRoom(
            null,
            $barcode,
            $targetRoomId,
            $this->inventurhinweisBuilder->forStandortwechsel($meldung, $actor, $roomSollLabel, $roomIstLabel),
            $settings?->inventurhinweisAnhaengen ?? true,
        );

        if ($result->success) {
            $meldung->update([
                'seventhings_status' => SeventhingsSyncStatus::Applied,
                'seventhings_error' => null,
                'seventhings_applied_at' => now(),
            ]);

            return SeventhingsSyncStatus::Applied;
        }

        $meldung->update([
            'seventhings_status' => SeventhingsSyncStatus::Failed,
            'seventhings_error' => $result->errorMessage,
        ]);

        return SeventhingsSyncStatus::Failed;
    }

    public function roomLabel(int $roomId): ?string
    {
        return $this->roomListService->labelForId($roomId);
    }

    private function resolveRaumIstLabel(Meldung $meldung): ?string
    {
        $raumIstId = $meldung->inventar['raum_ist_id'] ?? null;
        if (is_numeric($raumIstId)) {
            $label = $this->roomListService->labelForId((int) $raumIstId);
            if ($label !== null) {
                return $label;
            }
        }

        $text = trim((string) ($meldung->inventar['raum_ist_text'] ?? ''));

        return $text !== '' ? $text : null;
    }
}
