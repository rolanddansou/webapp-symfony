<?php

namespace App\Controller\Admin\Field;

use App\Form\Admin\MerchantStoreScheduleFormType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class ScheduleField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Weekly Schedule')
            ->setTemplatePath('admin/field/schedule.html.twig')
            ->setFormType(CollectionType::class)
            ->setFormTypeOptions([
                'entry_type' => MerchantStoreScheduleFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => $label ?? 'Weekly Schedule',
                'attr' => [
                    'class' => 'schedules-collection',
                    'data-schedule-widget' => 'true',
                ],
            ])
            ->addCssClass('field-schedule')
            ->addJsFiles('build/admin.js');
    }

    public function setMaxSchedules(int $max): self
    {
        $this->setCustomOption('maxSchedules', $max);
        return $this;
    }

    public function showDayName(bool $show = true): self
    {
        $this->setCustomOption('showDayName', $show);
        return $this;
    }
}
