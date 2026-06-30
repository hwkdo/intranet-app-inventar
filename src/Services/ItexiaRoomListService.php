<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Services;

use Hwkdo\SeventhingsLaravel\SeventhingsLaravel;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ItexiaRoomListService
{
    public function __construct(
        private readonly SeventhingsLaravel $seventhings,
    ) {}

    /**
     * @return list<array{id: int, nummer: string, label: string, name: string, gebaeude: string|null, etage: string|null, kostenstelle: string|null, mitarbeiter: string|null}>
     */
    public function all(): array
    {
        return Cache::remember('intranet-app-inventar:itexia-raeume', 3600, function (): array {
            try {
                $rooms = $this->seventhings->getRaeume();
            } catch (Throwable) {
                return [];
            }

            $out = [];
            foreach ($rooms as $room) {
                $out[] = [
                    'id' => (int) $room->id,
                    'nummer' => (string) ($room->nummer ?? ''),
                    'label' => (string) ($room->label ?? ''),
                    'name' => (string) ($room->name ?? ''),
                    'gebaeude' => $room->gebaeude ?? null,
                    'etage' => $room->etage ?? null,
                    'kostenstelle' => $room->kostenstelle ?? null,
                    'mitarbeiter' => $room->mitarbeiter ?? null,
                ];
            }

            usort($out, fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

            return $out;
        });
    }

    public function labelForId(?int $roomId): ?string
    {
        if ($roomId === null) {
            return null;
        }

        foreach ($this->all() as $room) {
            if ($room['id'] === $roomId) {
                return $room['label'] !== '' ? $room['label'] : $room['nummer'];
            }
        }

        try {
            $room = $this->seventhings->findRaumById($roomId);

            return $room?->label ?? $room?->nummer;
        } catch (Throwable) {
            return null;
        }
    }
}
