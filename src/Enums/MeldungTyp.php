<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Enums;

enum MeldungTyp: string
{
    case Aussonderung = 'aussonderung';
    case Standortwechsel = 'standortwechsel';
}
