<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\Meldung;

it('serialisiert inventar meldung mail fuer die queue ohne itexia raum modelle', function (): void {
    $user = User::factory()->create();

    $meldung = Meldung::query()->create([
        'user_id' => $user->id,
        'typ' => MeldungTyp::Standortwechsel,
        'inventar' => ['beschreibung' => 'Laptop', 'barcode' => '12345', 'raum_ist_id' => 1],
        'data' => ['raum_soll_id' => 42],
    ]);

    $mail = new InventarMeldungMail($meldung, [], 'Raum Alt', 'Raum Neu');

    $restored = unserialize(serialize($mail));

    expect($restored)->toBeInstanceOf(InventarMeldungMail::class)
        ->and($restored->raumIstLabel)->toBe('Raum Alt')
        ->and($restored->raumSollLabel)->toBe('Raum Neu')
        ->and($restored->meldung->is($meldung))->toBeTrue();
});
