<?php

namespace App\Feature\Helper;

use IntlDateFormatter;

class DateHelper
{
    public static function format(\DateTimeInterface|null $dateTime = null): bool|string|null
    {
        if (!$dateTime) {
            return null;
        }

        $formatter = new IntlDateFormatter(
            'fr_FR',                     // Locale: French (France)
            IntlDateFormatter::NONE,     // Date style: NONE because we are using a custom pattern
            IntlDateFormatter::NONE      // Time style: NONE because we are only formatting the date
        );

        $formatter->setPattern('d MMM y HH:mm');

        $val = $formatter->format($dateTime);

        if ($val) {
            return $val;
        }

        return "";
    }

    public static function formatDate(\DateTimeInterface|null $dateTime = null): bool|string|null
    {
        if (!$dateTime) {
            return null;
        }

        $formatter = new IntlDateFormatter(
            'fr_FR',                     // Locale: French (France)
            IntlDateFormatter::NONE,     // Date style: NONE because we are using a custom pattern
            IntlDateFormatter::NONE      // Time style: NONE because we are only formatting the date
        );

        $formatter->setPattern('d MMMM y');

        $val = $formatter->format($dateTime);

        if ($val) {
            return $val;
        }

        return "";
    }

    public static function formatForBusEvent(\DateTimeInterface $date): string
    {
        return $date->format("Y-m-d H:i:s");
    }

    public static function nowUTC(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(Timezones::UTC));
    }

    public static function toUTC(\DateTimeInterface $date): \DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable && $date->getTimezone()->getName() === Timezones::UTC) {
            return $date;
        }

        return \DateTimeImmutable::createFromInterface($date)->setTimezone(new \DateTimeZone(Timezones::UTC));
    }
}
