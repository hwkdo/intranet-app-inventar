<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\MeldungSubmissionService;
use Hwkdo\SeventhingsLaravel\Data\ItexiaRoomUpdateResult;
use Hwkdo\SeventhingsLaravel\Services\ItexiaRoomUpdateService;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

beforeEach(function (): void {
    Mail::fake();
});

it('speichert standortwechsel und setzt seventhings status applied', function (): void {
    $user = User::factory()->create();

    mock(ItexiaRoomUpdateService::class)
        ->shouldReceive('updateActualAndTargetRoom')
        ->once()
        ->with(null, '12345', 42, Mockery::type('string'), true)
        ->andReturn(ItexiaRoomUpdateResult::success('uuid-1', 42));

    actingAs($user);

    $results = app(MeldungSubmissionService::class)->submitStandortwechsel($user, [[
        'inventar' => ['barcode' => '12345', 'beschreibung' => 'Testgerät', 'raum_ist_id' => 1],
        'data' => ['raum_soll_id' => 42],
    ]]);

    expect($results)->toHaveCount(1)
        ->and($results[0]['status'])->toBe(SeventhingsSyncStatus::Applied);

    $meldung = Meldung::query()->first();
    expect($meldung)->not->toBeNull()
        ->and($meldung->typ)->toBe(MeldungTyp::Standortwechsel)
        ->and($meldung->seventhings_status)->toBe(SeventhingsSyncStatus::Applied);

    Mail::assertQueued(InventarMeldungMail::class, 1);
});

it('setzt skipped wenn kein barcode beim standortwechsel', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    mock(ItexiaRoomUpdateService::class)->shouldNotReceive('updateActualAndTargetRoom');

    $results = app(MeldungSubmissionService::class)->submitStandortwechsel($user, [[
        'inventar' => ['beschreibung' => 'Ohne Barcode', 'raum_ist_text' => 'A101'],
        'data' => ['raum_soll_id' => 42],
    ]]);

    expect($results[0]['status'])->toBe(SeventhingsSyncStatus::Skipped);
});
