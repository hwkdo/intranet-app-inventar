<?php

namespace Hwkdo\IntranetAppInventar;
use Hwkdo\IntranetAppBase\Interfaces\IntranetAppInterface;
use Illuminate\Support\Collection;

class IntranetAppInventar implements IntranetAppInterface 
{
    public static function app_name(): string
    {
        return 'Inventar';
    }

    public static function app_icon(): string
    {
        return 'archive-box';
    }

    public static function identifier(): string
    {
        return 'inventar';
    }

    public static function roles_admin(): Collection
    {
        return collect(config('intranet-app-inventar.roles.admin'));
    }

    public static function roles_user(): Collection
    {
        return collect(config('intranet-app-inventar.roles.user'));
    }
    
    public static function userSettingsClass(): ?string
    {
        return \Hwkdo\IntranetAppInventar\Data\UserSettings::class;
    }
    
    public static function appSettingsClass(): ?string
    {
        return \Hwkdo\IntranetAppInventar\Data\AppSettings::class;
    }

    public static function mcpServers(): array
    {
        return [];
    }
}
