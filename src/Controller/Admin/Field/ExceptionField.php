<?php

namespace App\Controller\Admin\Field;

use App\Form\Admin\ScheduleExceptionFormType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class ExceptionField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label ?? 'Schedule Exceptions')
            ->setTemplatePath('admin/field/exception.html.twig')
            ->setFormType(CollectionType::class)
            ->setFormTypeOptions([
                'entry_type' => ScheduleExceptionFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => $label ?? 'Schedule Exceptions',
                'attr' => [
                    'class' => 'exceptions-collection',
                    'data-exception-widget' => 'true',
                ],
            ])
            ->addCssClass('field-exception')
            ->addJsFiles('build/admin.js');
    }

    public function showPastExceptions(bool $show = false): self
    {
        $this->setCustomOption('showPast', $show);
        return $this;
    }
}
