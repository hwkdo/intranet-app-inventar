<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppInventar\Data\AppSettings;
use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Mail\InventarMeldungMail;
use Hwkdo\IntranetAppInventar\Models\IntranetAppInventarSettings;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Hwkdo\IntranetAppInventar\Services\MeldungNotificationService;
use Hwkdo\IntranetAppInventar\Support\VorgesetztenResolver;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    Mail::fake();

    IntranetAppInventarSettings::query()->delete();
    IntranetAppInventarSettings::create([
        'version' => 1,
        'settings' => AppSettings::from([
            'mailEmpfaenger' => 'asset@hwk-do.de',
            'mailEmpfaengerRollen' => [],
            'testing' => false,
        ])->toArray(),
    ]);
});

it('stellt genau eine mail mit allen empfaengern und cc an den melder in die warteschlange', function (): void {
    $actor = User::factory()->create(['email' => 'melder@example.com']);
    $vorgesetzterEins = User::factory()->create(['email' => 'vg1@example.com']);
    $vorgesetzterZwei = User::factory()->create(['email' => 'vg2@example.com']);

    mock(VorgesetztenResolver::class)
        ->shouldReceive('vorgesetzteOhneHgf')
        ->once()
        ->with(Mockery::on(fn (User $user): bool => $user->is($actor)))
        ->andReturn(collect([$vorgesetzterEins, $vorgesetzterZwei]));

    $meldung = Meldung::query()->create([
        'user_id' => $actor->id,
        'typ' => MeldungTyp::Standortwechsel,
        'inventar' => ['barcode' => '12345', 'beschreibung' => 'Test'],
        'data' => ['raum_soll_id' => 42],
    ]);

    app(MeldungNotificationService::class)->notify($meldung, $actor);

    Mail::assertQueued(InventarMeldungMail::class, 1);
    Mail::assertQueued(InventarMeldungMail::class, function (InventarMeldungMail $mail) use ($meldung): bool {
        return $mail->hasTo('asset@hwk-do.de')
            && $mail->hasTo('vg1@example.com')
            && $mail->hasTo('vg2@example.com')
            && $mail->hasCc('melder@example.com')
            && $mail->meldung->is($meldung);
    });
});

it('sendet im testmodus nur eine mail an den melder ohne cc', function (): void {
    IntranetAppInventarSettings::query()->delete();
    IntranetAppInventarSettings::create([
        'version' => 1,
        'settings' => AppSettings::from([
            'mailEmpfaenger' => 'asset@hwk-do.de',
            'testing' => true,
        ])->toArray(),
    ]);

    $actor = User::factory()->create(['email' => 'melder@example.com']);

    mock(VorgesetztenResolver::class)->shouldNotReceive('vorgesetzteOhneHgf');

    $meldung = Meldung::query()->create([
        'user_id' => $actor->id,
        'typ' => MeldungTyp::Standortwechsel,
        'inventar' => ['barcode' => '12345'],
        'data' => [],
    ]);

    app(MeldungNotificationService::class)->notify($meldung, $actor);

    Mail::assertQueued(InventarMeldungMail::class, 1);
    Mail::assertQueued(InventarMeldungMail::class, function (InventarMeldungMail $mail): bool {
        return $mail->hasTo('melder@example.com') && ! $mail->hasCc('melder@example.com');
    });
});
