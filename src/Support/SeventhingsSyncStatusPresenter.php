<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Support;

use Hwkdo\IntranetAppInventar\Data\SeventhingsSyncSummaryLine;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncAction;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;

class SeventhingsSyncStatusPresenter
{
    public function headline(SeventhingsSyncSummaryLine $line): string
    {
        if ($line->action === SeventhingsSyncAction::Archive) {
            return match ($line->status) {
                SeventhingsSyncStatus::Applied => 'Itexia/Seventhings: Objekt wurde automatisch archiviert und der Inventurhinweis gesetzt.',
                SeventhingsSyncStatus::Failed => 'Itexia/Seventhings: Automatische Archivierung fehlgeschlagen — bitte manuell in Seventhings archivieren.',
                SeventhingsSyncStatus::Skipped => filled($line->errorMessage)
                    ? (string) $line->errorMessage
                    : 'Itexia/Seventhings: Keine automatische Archivierung — manuelle Umsetzung in Seventhings erforderlich.',
                SeventhingsSyncStatus::Pending => 'Itexia/Seventhings: Archivierung ausstehend.',
            };
        }

        return match ($line->status) {
            SeventhingsSyncStatus::Applied => 'Itexia/Seventhings: Raum wurde automatisch gesetzt und der Inventurhinweis ergänzt'
                .($line->roomLabel ? " ({$line->roomLabel})." : ' (actual_room und target_room).'),
            SeventhingsSyncStatus::Failed => 'Itexia/Seventhings: Automatische Raumänderung fehlgeschlagen — bitte manuell durch einen Seventhings-Admin umsetzen.',
            SeventhingsSyncStatus::Skipped => filled($line->errorMessage)
                ? (string) $line->errorMessage
                : 'Itexia/Seventhings: Kein automatisches Update — manuelle Umsetzung in Seventhings erforderlich.',
            SeventhingsSyncStatus::Pending => 'Itexia/Seventhings: Update ausstehend.',
        };
    }

    public function detail(SeventhingsSyncSummaryLine $line): ?string
    {
        if ($line->status === SeventhingsSyncStatus::Failed && filled($line->errorMessage)) {
            return 'Fehler: '.$line->errorMessage;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $inventar
     */
    public function assetLabel(array $inventar): string
    {
        $parts = array_filter([
            $inventar['barcode'] ?? null,
            $inventar['beschreibung'] ?? null,
            $inventar['sn'] ?? null,
        ]);

        return $parts !== [] ? implode(' — ', $parts) : 'Unbekanntes Asset';
    }
}
