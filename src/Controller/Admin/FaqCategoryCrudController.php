<?php

namespace App\Controller\Admin;

use App\Entity\System\FaqCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FaqCategoryCrudController extends SharedAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FaqCategory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('icon', 'Icône (FontAwesome)');
        yield IntegerField::new('position', 'Position');
    }
}
