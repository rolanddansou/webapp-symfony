<?php

namespace App\Controller\Admin\Field;

use App\Feature\Helper\Timezones;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class SmartDateTimeField implements FieldInterface
{
    use FieldTrait;

    public const DEFAULT_FORMAT = 'dd/MM/yyyy HH:mm';

    public static function new(string $propertyName, ?string $label = null): DateTimeField
    {
        return DateTimeField::new($propertyName, $label)
            ->setTimezone(Timezones::AFRICA_PORTO_NOVO)
            ->setFormat(self::DEFAULT_FORMAT)
            ->setFormTypeOptions([
                'html5' => true,
                'view_timezone' => Timezones::AFRICA_PORTO_NOVO,
                'model_timezone' => Timezones::UTC,
            ]);
    }
}
