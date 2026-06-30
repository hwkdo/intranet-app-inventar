<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppInventar\Data\SeventhingsSyncSummaryLine;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncAction;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Models\Meldung;

class MeldungSubmissionService
{
    public function __construct(
        private readonly StandortwechselApplyService $standortwechselApplyService,
        private readonly AussonderungApplyService $aussonderungApplyService,
        private readonly MeldungNotificationService $notificationService,
    ) {}

    /**
     * @param  list<array{inventar: array<string, mixed>, data: array<string, mixed>}>  $entries
     * @return list<array{meldung_id: int, status: SeventhingsSyncStatus}>
     */
    public function submitStandortwechsel(User $actor, array $entries): array
    {
        $results = [];
        $syncSummary = [];
        $primaryMeldung = null;

        foreach ($entries as $entry) {
            $meldung = Meldung::query()->create([
                'user_id' => $actor->id,
                'typ' => MeldungTyp::Standortwechsel,
                'inventar' => $entry['inventar'],
                'data' => $entry['data'],
                'seventhings_status' => SeventhingsSyncStatus::Pending,
            ]);

            $targetRoomId = (int) ($entry['data']['raum_soll_id'] ?? 0);
            $status = $this->standortwechselApplyService->apply($meldung, $targetRoomId, $actor);
            $meldung->refresh();

            $syncSummary[] = new SeventhingsSyncSummaryLine(
                inventar: $entry['inventar'],
                status: $status,
                errorMessage: $meldung->seventhings_error,
                roomLabel: $this->standortwechselApplyService->roomLabel($targetRoomId),
            );

            $results[] = ['meldung_id' => $meldung->id, 'status' => $status];
            $primaryMeldung ??= $meldung;
        }

        if ($primaryMeldung !== null) {
            $this->notificationService->notify($primaryMeldung, $actor, $syncSummary);
        }

        return $results;
    }

    /**
     * @param  list<array{inventar: array<string, mixed>, data: array<string, mixed>}>  $entries
     * @return list<array{meldung_id: int, status: SeventhingsSyncStatus}>
     */
    public function submitAussonderung(User $actor, array $entries): array
    {
        $results = [];
        $syncSummary = [];
        $primaryMeldung = null;

        foreach ($entries as $entry) {
            $meldung = Meldung::query()->create([
                'user_id' => $actor->id,
                'typ' => MeldungTyp::Aussonderung,
                'inventar' => $entry['inventar'],
                'data' => $entry['data'],
                'seventhings_status' => SeventhingsSyncStatus::Pending,
            ]);

            $applyResult = $this->aussonderungApplyService->apply($meldung, $actor);
            $meldung->refresh();

            $syncSummary[] = new SeventhingsSyncSummaryLine(
                inventar: $entry['inventar'],
                status: $applyResult['status'],
                action: SeventhingsSyncAction::Archive,
                errorMessage: $applyResult['errorMessage'],
            );

            $results[] = [
                'meldung_id' => $meldung->id,
                'status' => $applyResult['status'],
            ];
            $primaryMeldung ??= $meldung;
        }

        if ($primaryMeldung !== null) {
            $this->notificationService->notify($primaryMeldung, $actor, $syncSummary);
        }

        return $results;
    }
}
