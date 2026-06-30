<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Models;

use Hwkdo\IntranetAppInventar\Enums\MeldungTyp;
use Hwkdo\IntranetAppInventar\Enums\SeventhingsSyncStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meldung extends Model
{
    protected $table = 'intranet_app_inventar_meldungen';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'inventar' => 'array',
            'data' => 'array',
            'typ' => MeldungTyp::class,
            'seventhings_status' => SeventhingsSyncStatus::class,
            'seventhings_applied_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\Illuminate\Database\Eloquent\Model, $this> */
    public function user(): BelongsTo
    {
        $userClass = config('intranet-app-inventar.user_model');

        return $this->belongsTo($userClass);
    }

    public function getGrundLabel(string $value): string
    {
        $labels = IntranetAppInventarSettings::current()?->settings->fieldLabels ?? [];
        $gruende = $labels['gruende'] ?? [];

        if (is_array($gruende) && isset($gruende[$value])) {
            return (string) $gruende[$value];
        }

        return ucfirst(str_replace('_', ' ', $value));
    }

    public function getLabel(string $key): string
    {
        $labels = IntranetAppInventarSettings::current()?->settings->fieldLabels ?? [];

        if ($key === 'grund1') {
            return (string) ($labels['grund1'] ?? 'Grund');
        }

        if (isset($labels[$key]) && is_string($labels[$key])) {
            return $labels[$key];
        }

        if ($key === 'grund1' && isset($labels['gruende']) && is_array($labels['gruende'])) {
            return (string) ($labels['gruende'][$this->data['grund1'] ?? ''] ?? ucfirst($key));
        }

        return ucfirst(str_replace('_', ' ', $key));
    }
}
