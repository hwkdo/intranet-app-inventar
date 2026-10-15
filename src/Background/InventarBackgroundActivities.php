<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Background;

use Hwkdo\IntranetAppBase\Data\BackgroundActivityDefinition;
use Hwkdo\IntranetAppInventar\IntranetAppInventar;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;

final class InventarBackgroundActivities
{
    /**
     * @return list<BackgroundActivityDefinition>
     */
    public static function definitions(): array
    {
        return [
            BackgroundActivityDefinition::mail(
                key: 'inventar.mail-meldung',
                label: 'Mail: Inventarmeldung',
                appIdentifier: IntranetAppInventar::identifier(),
                appName: IntranetAppInventar::app_name(),
                mailable: InventarMeldungMail::class,
            ),
        ];
    }
}
