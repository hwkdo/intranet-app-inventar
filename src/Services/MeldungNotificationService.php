<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppInventar\Data\SeventhingsSyncSummaryLine;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Support\VorgesetztenResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Throwable;

class MeldungNotificationService
{
    public function __construct(
        private readonly VorgesetztenResolver $vorgesetztenResolver,
        private readonly ItexiaRoomListService $roomListService,
    ) {}

    /**
     * @param  list<SeventhingsSyncSummaryLine>  $syncSummary
     */
    public function notify(Meldung $meldung, User $actor, array $syncSummary = []): void
    {
        $settings = IntranetAppInventarSettings::current()?->settings;
        $testing = $settings?->testing ?? false;
        $recipients = $this->resolveRecipients($actor, $testing);

        if ($recipients->isEmpty()) {
            return;
        }

        $raumIstLabel = $this->resolveRaumIstLabel($meldung);
        $raumSollId = $meldung->data['raum_soll_id'] ?? null;
        $raumSollLabel = is_numeric($raumSollId)
            ? $this->roomListService->labelForId((int) $raumSollId)
            : null;

        $mailable = new InventarMeldungMail($meldung, $syncSummary, $raumIstLabel, $raumSollLabel);

        $pendingMail = Mail::to($recipients->all());

        $actorEmail = filled($actor->email) ? trim((string) $actor->email) : null;
        if (
            ! $testing
            && $actorEmail !== null
            && ! $recipients->contains(fn (string $email): bool => strcasecmp($email, $actorEmail) === 0)
        ) {
            $pendingMail->cc($actorEmail);
        }

        try {
            $pendingMail->queue($mailable);
        } catch (Throwable $exception) {
            Log::warning('Inventar-Meldung konnte nicht in die Mail-Queue gestellt werden.', [
                'meldung_id' => $meldung->id,
                'recipients' => $recipients->all(),
                'cc' => $actorEmail,
                'error' => $exception->getMessage(),
            ]);
        }
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

    /**
     * @return Collection<int, string>
     */
    private function resolveRecipients(User $actor, bool $testing): Collection
    {
        if ($testing) {
            return collect([$actor->email])->filter();
        }

        $settings = IntranetAppInventarSettings::current()?->settings;
        $emails = collect();

        if ($settings !== null) {
            foreach (preg_split('/[\s,;]+/', $settings->mailEmpfaenger) ?: [] as $part) {
                $email = trim((string) $part);
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails->push($email);
                }
            }

            foreach ($settings->mailEmpfaengerRollen as $roleName) {
                if (! is_string($roleName) || $roleName === '') {
                    continue;
                }
                try {
                    $role = Role::findByName($roleName);
                    foreach ($role->users as $user) {
                        if (filled($user->email)) {
                            $emails->push($user->email);
                        }
                    }
                } catch (Throwable) {
                    continue;
                }
            }
        }

        foreach ($this->vorgesetztenResolver->vorgesetzteOhneHgf($actor) as $vorgesetzter) {
            if (filled($vorgesetzter->email)) {
                $emails->push($vorgesetzter->email);
            }
        }

        return $emails->unique()->values();
    }
}
