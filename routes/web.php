<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'can:see-app-inventar'])->group(function (): void {
    Route::livewire('apps/inventar', 'intranet-app-inventar::apps.inventar.index')->name('apps.inventar.index');
    Route::livewire('apps/inventar/aussonderung', 'intranet-app-inventar::apps.inventar.aussonderung')->name('apps.inventar.aussonderung');
    Route::livewire('apps/inventar/standortwechsel', 'intranet-app-inventar::apps.inventar.standortwechsel')->name('apps.inventar.standortwechsel');
    Route::livewire('apps/inventar/settings/user', 'intranet-app-inventar::apps.inventar.settings.user')->name('apps.inventar.settings.user');
    Route::livewire('apps/inventar/info', 'intranet-app-inventar::apps.inventar.info')->name('apps.inventar.info');
});

Route::middleware(['web', 'auth', 'can:manage-app-inventar'])->group(function (): void {
    Route::livewire('apps/inventar/admin', 'intranet-app-inventar::apps.inventar.admin.index')->name('apps.inventar.admin.index');
    Route::redirect('apps/inventar/admin/meldungen', '/apps/inventar/admin?tab=meldungen')->name('apps.inventar.admin.meldungen');
});
