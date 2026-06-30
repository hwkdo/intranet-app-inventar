<?php

declare(strict_types=1);

use Hwkdo\IntranetAppInventar\Data\SeventhingsSyncSummaryLine;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncAction;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Hwkdo\IntranetAppInventar\Support\SeventhingsSyncStatusPresenter;

it('formuliert applied status in mail text', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: ['barcode' => '99', 'beschreibung' => 'Laptop'],
        status: SeventhingsSyncStatus::Applied,
        roomLabel: 'Raum 12',
    );

    expect($presenter->headline($line))->toContain('automatisch gesetzt');
});

it('formuliert standortwechsel applied status mit inventurhinweis', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: ['barcode' => '99', 'beschreibung' => 'Laptop'],
        status: SeventhingsSyncStatus::Applied,
        roomLabel: 'Raum 12',
    );

    expect($presenter->headline($line))->toContain('Inventurhinweis');
});

it('formuliert failed status mit hinweis auf manuelle umsetzung', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: ['barcode' => '99'],
        status: SeventhingsSyncStatus::Failed,
        errorMessage: 'API down',
    );

    expect($presenter->headline($line))->toContain('manuell');
    expect($presenter->detail($line))->toContain('API down');
});

it('formuliert skipped status mit hinweis auf manuelle umsetzung', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: [],
        status: SeventhingsSyncStatus::Skipped,
    );

    expect($presenter->headline($line))->toContain('manuelle Umsetzung');
});

it('formuliert archivierung applied status', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: ['barcode' => '99'],
        status: SeventhingsSyncStatus::Applied,
        action: SeventhingsSyncAction::Archive,
    );

    expect($presenter->headline($line))->toContain('archiviert');
});

it('formuliert deaktivierte archivierung in der mail', function (): void {
    $presenter = new SeventhingsSyncStatusPresenter;
    $line = new SeventhingsSyncSummaryLine(
        inventar: ['barcode' => '99'],
        status: SeventhingsSyncStatus::Skipped,
        action: SeventhingsSyncAction::Archive,
        errorMessage: 'Automatische Archivierung ist in den Administrator-Einstellungen deaktiviert.',
    );

    expect($presenter->headline($line))->toContain('deaktiviert');
});
