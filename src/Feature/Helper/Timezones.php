<?php

namespace App\Feature\Helper;

class Timezones
{
    // Définition des timezones principales comme constantes
    public const AFRICA_PORTO_NOVO = 'Africa/Porto-Novo';
    public const AFRICA_ABIDJAN    = 'Africa/Abidjan';
    public const AFRICA_LAGOS      = 'Africa/Lagos';
    public const EUROPE_PARIS      = 'Europe/Paris';
    public const EUROPE_LONDON     = 'Europe/London';
    public const AMERICA_NEW_YORK  = 'America/New_York';
    public const AMERICA_LOS_ANGELES = 'America/Los_Angeles';
    public const ASIA_TOKYO        = 'Asia/Tokyo';
    public const ASIA_SHANGHAI     = 'Asia/Shanghai';
    public const UTC               = 'UTC';

    /**
     * Retourne toutes les timezones disponibles dans ce helper
     */
    public static function all(): array
    {
        return [
            self::AFRICA_PORTO_NOVO,
            self::AFRICA_ABIDJAN,
            self::AFRICA_LAGOS,
            self::EUROPE_PARIS,
            self::EUROPE_LONDON,
            self::AMERICA_NEW_YORK,
            self::AMERICA_LOS_ANGELES,
            self::ASIA_TOKYO,
            self::ASIA_SHANGHAI,
            self::UTC,
        ];
    }
}
