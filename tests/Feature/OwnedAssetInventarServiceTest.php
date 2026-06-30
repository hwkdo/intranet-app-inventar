<?php

declare(strict_types=1);

use App\Models\User;
use Hwkdo\IntranetAppAssets\Models\Asset;
use Hwkdo\IntranetAppInventar\Services\ItexiaAssetLookupService;
use Hwkdo\IntranetAppInventar\Services\OwnedAssetInventarService;

use function Pest\Laravel\mock;

it('listet nur assets des aktuellen nutzers', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Asset::query()->create([
        'user_id' => $user->id,
        'name' => 'Mein Laptop',
        'model' => 'X1',
    ]);

    Asset::query()->create([
        'user_id' => $other->id,
        'name' => 'Fremdes Gerät',
        'model' => 'Y2',
    ]);

    $options = app(OwnedAssetInventarService::class)->optionsForUser($user);

    expect($options)->toHaveCount(1)
        ->and($options->first()['label'])->toContain('Mein Laptop');
});

it('nutzt itexia lookup wenn owned asset eine itexia id hat', function (): void {
    $user = User::factory()->create();

    $asset = Asset::query()->create([
        'user_id' => $user->id,
        'name' => 'Drucker',
        'model' => 'HP',
        'itexia_id' => '12345',
        'serial_number' => 'SN-1',
    ]);

    mock(ItexiaAssetLookupService::class)
        ->shouldReceive('findByBarcode')
        ->once()
        ->with('12345')
        ->andReturn([
            'found' => true,
            'data' => [
                'barcode' => '12345',
                'beschreibung' => 'Itexia Drucker',
                'sn' => 'SN-ITX',
                'raum_ist_id' => 7,
            ],
        ]);

    $result = app(OwnedAssetInventarService::class)->resolveSelection($user, (int) $asset->id);

    expect($result['ok'])->toBeTrue()
        ->and($result['data']['barcode'])->toBe('12345')
        ->and($result['data']['beschreibung'])->toBe('Itexia Drucker')
        ->and($result['data']['raum_ist_id'])->toBe(7);
});

it('mappt owned asset manuell wenn keine itexia id vorhanden ist', function (): void {
    $user = User::factory()->create();

    $asset = Asset::query()->create([
        'user_id' => $user->id,
        'name' => 'Stuhl',
        'model' => 'Büro',
        'serial_number' => 'CHAIR-99',
        'location' => 'Raum 101',
    ]);

    mock(ItexiaAssetLookupService::class)->shouldNotReceive('findByBarcode');

    $result = app(OwnedAssetInventarService::class)->resolveSelection($user, (int) $asset->id);

    expect($result['ok'])->toBeTrue()
        ->and($result['data']['barcode'])->toBeNull()
        ->and($result['data']['beschreibung'])->toBe('Stuhl')
        ->and($result['data']['sn'])->toBe('CHAIR-99')
        ->and($result['data']['raum_ist_text'])->toBe('Raum 101');
});

it('lehnt fremde assets ab', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $asset = Asset::query()->create([
        'user_id' => $other->id,
        'name' => 'Nicht meins',
        'model' => 'X',
    ]);

    $result = app(OwnedAssetInventarService::class)->resolveSelection($user, (int) $asset->id);

    expect($result['ok'])->toBeFalse();
});
