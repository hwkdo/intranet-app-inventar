<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Data\AppSettings;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\MeldungSubmissionService;
use Hwkdo\SeventhingsLaravel\Data\ItexiaAssetArchiveResult;
use Hwkdo\SeventhingsLaravel\Services\ItexiaAssetArchiveService;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

beforeEach(function (): void {
    Mail::fake();

    IntranetAppInventarSettings::query()->delete();
    IntranetAppInventarSettings::create([
        'version' => 1,
        'settings' => AppSettings::from([
            'seventhingsArchivierenBeiAnlagenabgang' => true,
            'mailEmpfaengerRollen' => [],
            'testing' => true,
        ])->toArray(),
    ]);
});

it('archiviert anlagenabgang in seventhings wenn aktiviert', function (): void {
    $user = User::factory()->create();

    mock(ItexiaAssetArchiveService::class)
        ->shouldReceive('archiveByBarcode')
        ->once()
        ->with(null, '12345', Mockery::type('string'), true)
        ->andReturn(ItexiaAssetArchiveResult::success('uuid-1'));

    actingAs($user);

    $results = app(MeldungSubmissionService::class)->submitAussonderung($user, [[
        'inventar' => ['barcode' => '12345', 'beschreibung' => 'Testgerät'],
        'data' => ['art_abgang' => 'verkauf'],
    ]]);

    expect($results[0]['status'])->toBe(SeventhingsSyncStatus::Applied);

    $meldung = Meldung::query()->first();
    expect($meldung)->not->toBeNull()
        ->and($meldung->typ)->toBe(MeldungTyp::Aussonderung)
        ->and($meldung->seventhings_status)->toBe(SeventhingsSyncStatus::Applied);

    Mail::assertQueued(InventarMeldungMail::class, function (InventarMeldungMail $mail): bool {
        return $mail->syncSummary !== []
            && $mail->syncSummary[0]->status === SeventhingsSyncStatus::Applied;
    });
});

it('meldet fehlgeschlagene archivierung in der mail', function (): void {
    $user = User::factory()->create();

    mock(ItexiaAssetArchiveService::class)
        ->shouldReceive('archiveByBarcode')
        ->once()
        ->with(null, '12345', Mockery::type('string'), true)
        ->andReturn(ItexiaAssetArchiveResult::failure('API nicht erreichbar'));

    actingAs($user);

    $results = app(MeldungSubmissionService::class)->submitAussonderung($user, [[
        'inventar' => ['barcode' => '12345'],
        'data' => [],
    ]]);

    expect($results[0]['status'])->toBe(SeventhingsSyncStatus::Failed);

    Mail::assertQueued(InventarMeldungMail::class, function (InventarMeldungMail $mail): bool {
        return $mail->syncSummary[0]->status === SeventhingsSyncStatus::Failed
            && $mail->syncSummary[0]->errorMessage === 'API nicht erreichbar';
    });
});

it('ueberspringt archivierung wenn admin deaktiviert', function (): void {
    IntranetAppInventarSettings::query()->delete();
    IntranetAppInventarSettings::create([
        'version' => 1,
        'settings' => AppSettings::from([
            'seventhingsArchivierenBeiAnlagenabgang' => false,
            'testing' => true,
        ])->toArray(),
    ]);

    $user = User::factory()->create();

    mock(ItexiaAssetArchiveService::class)->shouldNotReceive('archiveByBarcode');

    actingAs($user);

    $results = app(MeldungSubmissionService::class)->submitAussonderung($user, [[
        'inventar' => ['barcode' => '12345'],
        'data' => [],
    ]]);

    expect($results[0]['status'])->toBe(SeventhingsSyncStatus::Skipped);

    Mail::assertQueued(InventarMeldungMail::class, function (InventarMeldungMail $mail): bool {
        return str_contains(
            (string) $mail->syncSummary[0]->errorMessage,
            'Administrator-Einstellungen deaktiviert',
        );
    });
});
