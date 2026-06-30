<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use App\Models\User;
use Hwkdo\IntranetAppInventar\Models\Meldung;

class MeldungInventurhinweisBuilder
{
    public function forStandortwechsel(
        Meldung $meldung,
        User $actor,
        ?string $roomSollLabel = null,
        ?string $roomIstLabel = null,
    ): string {
        $lines = [
            sprintf(
                'Intranet-Standortwechsel am %s durch %s.',
                now()->format('d.m.Y H:i'),
                $actor->name,
            ),
        ];

        $beschreibung = $meldung->inventar['beschreibung'] ?? null;
        if (is_string($beschreibung) && $beschreibung !== '') {
            $lines[] = $beschreibung;
        }

        $barcode = trim((string) ($meldung->inventar['barcode'] ?? ''));
        if ($barcode !== '') {
            $lines[] = 'Itexia-ID: '.$barcode;
        }

        if (filled($roomIstLabel)) {
            $lines[] = 'Raum bisher: '.$roomIstLabel;
        }

        if (filled($roomSollLabel)) {
            $lines[] = 'Raum neu: '.$roomSollLabel;
        }

        return implode("\n", array_filter($lines, fn (string $line): bool => trim($line) !== ''));
    }

    public function forAnlagenabgang(Meldung $meldung, User $actor): string
    {
        $lines = [
            sprintf(
                'Intranet-Anlagenabgang am %s durch %s.',
                now()->format('d.m.Y H:i'),
                $actor->name,
            ),
            $this->buildAnlagenabgangSummary($meldung),
        ];

        $grund1 = $meldung->data['grund1'] ?? null;
        if (is_string($grund1) && $grund1 !== '') {
            $lines[] = 'Grund: '.$meldung->getGrundLabel($grund1);
        }

        $datum = $meldung->data['datum'] ?? null;
        if (is_string($datum) && $datum !== '') {
            $lines[] = 'Festgestellt am: '.$datum;
        }

        return implode("\n", array_filter($lines, fn (string $line): bool => trim($line) !== ''));
    }

    private function buildAnlagenabgangSummary(Meldung $meldung): string
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
