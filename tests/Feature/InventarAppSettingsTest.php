<?php

declare(strict_types=1);

use Hwkdo\IntranetAppInventar\Data\AppSettings;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;

it('hat testmodus standardmaessig deaktiviert', function (): void {
    expect((new AppSettings)->testing)->toBeFalse()
        ->and((new AppSettings)->seventhingsArchivierenBeiAnlagenabgang)->toBeFalse()
        ->and((new AppSettings)->inventurhinweisAnhaengen)->toBeTrue();
});

it('speichert testmodus in den app settings', function (): void {
    IntranetAppInventarSettings::query()->delete();
    IntranetAppInventarSettings::create([
        'version' => 1,
        'settings' => AppSettings::from(['testing' => true])->toArray(),
    ]);

    expect(IntranetAppInventarSettings::current()?->settings->testing)->toBeTrue();
});
