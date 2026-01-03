<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\SmartDateTimeField;
use App\Entity\Access\UserProfile\UserProfile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserProfileCrudController extends SharedAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("User Profile")
            ->setEntityLabelInPlural('Customers')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'User Profiles')
            ->setPageTitle('detail', 'User Profile Details');
    }

    public function configureActions(Actions $actions): Actions
    {
        $parentActions = parent::configureActions($actions);
        return $parentActions->disable(Action::NEW , Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
        yield TextField::new('fullName');
        yield TextField::new('user.userEmail')->setLabel('Email');
        yield TelephoneField::new('phoneNumber');
        yield SmartDateTimeField::new('createdAt');
    }
}
