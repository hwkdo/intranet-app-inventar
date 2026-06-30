<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Enums;

enum SeventhingsSyncStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
