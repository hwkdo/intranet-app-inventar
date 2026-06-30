<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Data;

use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncAction;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;

readonly class SeventhingsSyncSummaryLine
{
    /**
     * @param  array<string, mixed>  $inventar
     */
    public function __construct(
        public array $inventar,
        public SeventhingsSyncStatus $status,
        public SeventhingsSyncAction $action = SeventhingsSyncAction::RoomUpdate,
        public ?string $errorMessage = null,
        public ?string $roomLabel = null,
    ) {}
}
