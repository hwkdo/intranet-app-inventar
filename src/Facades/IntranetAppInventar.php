<?php

namespace Hwkdo\IntranetAppInventar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hwkdo\IntranetAppInventar\IntranetAppInventar
 */
class IntranetAppInventar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hwkdo\IntranetAppInventar\IntranetAppInventar::class;
    }
}
