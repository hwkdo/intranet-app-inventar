<?php

namespace Hwkdo\IntranetAppInventar\Models;

use Hwkdo\IntranetAppInventar\Data\AppSettings;
use Illuminate\Database\Eloquent\Model;

class IntranetAppInventarSettings extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => AppSettings::class.':default',
        ];
    }

    public static function current(): IntranetAppInventarSettings|null
    {
        return self::orderBy('version', 'desc')->first();
    }
}
