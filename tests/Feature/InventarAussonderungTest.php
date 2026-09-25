<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Data\AppSettings;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\ItexiaRoomListService;
use Hwkdo\IntranetAppInventar\Services\MeldungSubmissionService;
use Hwkdo\SeventhingsLaravel\Data\ItexiaAssetArchiveResult;
use Hwkdo\SeventhingsLaravel\Services\ItexiaAssetArchiveService;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

beforeEach(function (): void {
    Mail::fake();
    Permission::findOrCreate('see-app-inventar', 'web');

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

it('fordert geschaetztes alter bei jedem verschrottungsgrund', function (string $grund): void {
    $rooms = mock(ItexiaRoomListService::class);
    $rooms->shouldReceive('all')->andReturn([])->byDefault();
    $rooms->shouldReceive('labelForId')->andReturn(null)->byDefault();

    $user = User::factory()->create();
    $user->givePermissionTo('see-app-inventar');
    actingAs($user);

    Livewire::test('intranet-app-inventar::apps.inventar.aussonderung')
        ->set('step', 2)
        ->set('inventar', [
            'beschreibung' => 'Bohrmaschine',
            'barcode' => null,
            'datev_nr' => null,
            'sn' => null,
            'lieferdatum' => null,
            'preis' => null,
            'raum_ist_id' => null,
            'raum_ist_text' => 'Werkstatt 1',
        ])
        ->set('datum', '2026-09-25')
        ->set('oeffentliche_mittel', 'Nein')
        ->set('art_abgang', 'Verschrottung / Entsorgung')
        ->set('grund1', $grund)
        ->set('grund2_alter_des_gegenstands', '')
        ->set('grund2_anderer_grund', $grund === 'grund_ist_anderer_grund' ? 'Sonstiges' : '')
        ->call('submit')
        ->assertHasErrors(['grund2_alter_des_gegenstands' => 'required']);
})->with([
    'bedienerfehler hwk' => 'grund_ist_bedienerfehler_mitarbeiter_hwk',
    'bedienerfehler dritte' => 'grund_ist_bedienerfehler_dritte',
    'unachtsamkeit hwk' => 'grund_ist_unachtsamkeit_mitarbeiter_hwk',
    'aeussere einfluesse' => 'grund_ist_aeussere_einfluesse',
    'totalverschleiss' => 'grund_ist_abnutzung',
    'ueberalterung' => 'grund_ist_ueberalterung',
    'anderer grund' => 'grund_ist_anderer_grund',
]);

it('nimmt ueberalterung mit alter entgegen und speichert die meldung', function (): void {
    $rooms = mock(ItexiaRoomListService::class);
    $rooms->shouldReceive('all')->andReturn([])->byDefault();
    $rooms->shouldReceive('labelForId')->andReturn(null)->byDefault();

    $user = User::factory()->create();
    $user->givePermissionTo('see-app-inventar');
    actingAs($user);

    mock(ItexiaAssetArchiveService::class)->shouldReceive('archiveByBarcode')->zeroOrMoreTimes();

    Livewire::test('intranet-app-inventar::apps.inventar.aussonderung')
        ->set('step', 2)
        ->set('inventar', [
            'beschreibung' => 'Bohrmaschine',
            'barcode' => null,
            'datev_nr' => null,
            'sn' => null,
            'lieferdatum' => null,
            'preis' => null,
            'raum_ist_id' => null,
            'raum_ist_text' => 'Werkstatt 1',
        ])
        ->set('datum', '2026-09-25')
        ->set('oeffentliche_mittel', 'Nein')
        ->set('art_abgang', 'Verschrottung / Entsorgung')
        ->set('grund1', 'grund_ist_ueberalterung')
        ->set('grund2_alter_des_gegenstands', '12')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $meldung = Meldung::query()->first();
    expect($meldung)->not->toBeNull()
        ->and($meldung->data['grund1'] ?? null)->toBe('grund_ist_ueberalterung')
        ->and($meldung->data['grund2_alter_des_gegenstands'] ?? null)->toBe('12')
        ->and($meldung->getGrundLabel('grund_ist_ueberalterung'))->toBe('Überalterung')
        ->and($meldung->getGrundLabel('grund_ist_abnutzung'))->toBe('Totalverschleiß-/abnutzung');
});
