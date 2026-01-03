<?php

namespace App\Controller\Admin;

use App\Entity\System\Enum\StaticContentType;
use App\Entity\System\StaticContent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StaticContentCrudController extends SharedAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StaticContent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ChoiceField::new('type')
            ->setColumns(6)
            ->setChoices(StaticContentType::cases())
            ->setRequired(true);
        yield TextField::new('title')->setColumns(6);
        yield TextEditorField::new('content')->setColumns(12);
    }
}
