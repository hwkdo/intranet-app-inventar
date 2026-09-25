<?php

declare(strict_types=1);

use Hwkdo\IntranetAppInventar\Services\ItexiaAssetLookupService;
use Hwkdo\SeventhingsLaravel\Models\Asset as ItexiaAsset;
use Hwkdo\SeventhingsLaravel\SeventhingsLaravel;

use function Pest\Laravel\mock;

it('mappt lieferdatum und historischen anschaffungspreis aus itexia', function (): void {
    $row = (object) [
        'barcode' => '4711',
        'inventory_name' => 'Laptop Dell',
        'custom_4' => 'SN-99',
        'custom_78' => 'DATEV-1',
        'purchasing_date' => '2020-03-15',
        'preis_hist_anschaffungskosten_eff27c3b' => '1299,50',
        'actual_room' => null,
    ];

    mock(SeventhingsLaravel::class)
        ->shouldReceive('findAsset')
        ->once()
        ->with('4711')
        ->andReturn(new ItexiaAsset($row));

    $result = app(ItexiaAssetLookupService::class)->findByBarcode('4711');

    expect($result['found'])->toBeTrue()
        ->and($result['data']['lieferdatum'])->toBe('15.03.2020')
        ->and($result['data']['preis'])->toBe('1299,50')
        ->and($result['data']['datev_nr'])->toBe('DATEV-1')
        ->and($result['data']['raum_ist_id'])->toBeNull();
});

it('mappt raum_ist_id aus actual_room ohne Raum-API-Aufruf', function (): void {
    $row = (object) [
        'barcode' => '4711',
        'inventory_name' => 'Monitor',
        'actual_room' => 42,
    ];

    $seventhings = mock(SeventhingsLaravel::class);
    $seventhings->shouldReceive('findAsset')
        ->once()
        ->with('4711')
        ->andReturn(new ItexiaAsset($row));
    $seventhings->shouldReceive('findRaumById')->never();

    $result = app(ItexiaAssetLookupService::class)->findByBarcode('4711');

    expect($result['found'])->toBeTrue()
        ->and($result['data']['raum_ist_id'])->toBe(42);
});

it('ignoriert actual_room 0 und leere raum-referenzen', function (): void {
    $row = (object) [
        'barcode' => '4711',
        'inventory_name' => 'Ohne Raum',
        'actual_room' => 0,
    ];

    mock(SeventhingsLaravel::class)
        ->shouldReceive('findAsset')
        ->once()
        ->with('4711')
        ->andReturn(new ItexiaAsset($row));

    $result = app(ItexiaAssetLookupService::class)->findByBarcode('4711');

    expect($result['found'])->toBeTrue()
        ->and($result['data']['raum_ist_id'])->toBeNull();
});
