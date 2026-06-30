<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\MeldungInventurhinweisBuilder;

it('baut inventurhinweis fuer standortwechsel', function (): void {
    $actor = User::factory()->create(['name' => 'Max Mustermann']);
    $meldung = new Meldung([
        'typ' => MeldungTyp::Standortwechsel,
        'inventar' => [
            'barcode' => '12345',
            'beschreibung' => 'Laptop Dell',
        ],
        'data' => ['raum_soll_id' => 42],
    ]);

    $text = app(MeldungInventurhinweisBuilder::class)->forStandortwechsel(
        $meldung,
        $actor,
        'Raum 101',
        'Raum 202',
    );

    expect($text)
        ->toContain('Intranet-Standortwechsel')
        ->toContain('Max Mustermann')
        ->toContain('Laptop Dell')
        ->toContain('Itexia-ID: 12345')
        ->toContain('Raum bisher: Raum 202')
        ->toContain('Raum neu: Raum 101');
});
