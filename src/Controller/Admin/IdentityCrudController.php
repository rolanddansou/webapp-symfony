<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\SmartDateTimeField;
use App\Entity\Access\Identity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class IdentityCrudController extends SharedAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Identity::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Identity')
            ->setEntityLabelInPlural('Identities')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email'])
            ->setPageTitle('index', 'User Identities')
            ->setPageTitle('new', 'Create Identity')
            ->setPageTitle('edit', 'Edit Identity')
            ->setPageTitle('detail', 'Identity Details');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnDetail()
            ->setHelp('User UUID');

        yield EmailField::new('email')
            ->setRequired(true)
            ->setColumns('col-md-6 col-12')
            ->setHelp('User email address');

        yield AssociationField::new('credentials')
            ->setLabel(false)
            ->hideOnIndex()
            ->setColumns('col-md-6 col-12')
            //->setHelp('Associated login credentials')
            ->renderAsEmbeddedForm(
                crudNewPageName: UserCredentialsCrudController::PASSWORD_CRUD_PAGE,
                crudEditPageName: UserCredentialsCrudController::PASSWORD_CRUD_PAGE
            )
        ;

        yield AssociationField::new('roles')
            ->setLabel('Roles')
            ->setHelp('User roles (ROLE_ADMIN, ROLE_MERCHANT, etc.)')
            ->setColumns(6)
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ]);

        yield SmartDateTimeField::new('emailVerifiedAt')
            ->hideOnIndex()
            ->setColumns(6)
            ->setHelp('When email was verified');

        yield BooleanField::new('emailVerified')
            ->renderAsSwitch(false)
            ->setColumns(6)
            ->setHelp('Whether email has been verified');

        yield AssociationField::new('devices')
            ->setLabel('Devices')
            ->formatValue(function ($value) {
                return $value->count();
            })
            ->hideOnForm()
            ->setHelp('Number of registered devices');

        yield SmartDateTimeField::new('lastLoginAt')
            ->hideOnForm();

        yield SmartDateTimeField::new('createdAt')
            ->hideOnIndex()
            ->hideOnForm();

        yield SmartDateTimeField::new('updatedAt')
            ->hideOnIndex()
            ->hideOnForm();
    }
}
