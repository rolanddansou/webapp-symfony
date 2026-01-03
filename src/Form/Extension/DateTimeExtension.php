<?php

namespace App\Form\Extension;

use App\Feature\Helper\Timezones;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        // Extension appliquée à tous ces types de champs
        return [DateTimeType::class, DateType::class, TimeType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'model_timezone' => Timezones::UTC,              // backend / DB
            'view_timezone'  => Timezones::AFRICA_PORTO_NOVO // affichage admin
        ]);
    }
}
