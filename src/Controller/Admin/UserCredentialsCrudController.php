<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\SmartDateTimeField;
use App\Entity\Access\UserCredentials;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCredentialsCrudController extends SharedAbstractCrudController
{
    public const PASSWORD_CRUD_PAGE = 'password_crud_page';

    public static function getEntityFqcn(): string
    {
        return UserCredentials::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User Credentials')
            ->setEntityLabelInPlural('User Credentials')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'User Credentials')
            ->setPageTitle('new', 'Create Credentials')
            ->setPageTitle('edit', 'Edit Credentials')
            ->setPageTitle('detail', 'Credentials Details');
    }

    public function configureFields(string $pageName): iterable
    {
        $isPasswordBase = $pageName === self::PASSWORD_CRUD_PAGE;
        if ($isPasswordBase) {
            yield TextField::new('plainPassword', 'Password')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setColumns(12)
                ->setHelp('Enter new password (will be hashed automatically)')
                ->setRequired(true)
            ;
            return;
        }

        yield IdField::new('id')
            ->onlyOnDetail()
            ->setHelp('Credentials UUID');

        yield AssociationField::new('relativeUser')
            ->setLabel('User Identity')
            ->setRequired(true)
            ->setHelp('Associated user identity');

        // Password field - write-only for security
        yield TextField::new('passwordHash', 'Password')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setHelp('Enter new password (will be hashed automatically)')
            ->setRequired(false);

        yield BooleanField::new('twoFactorEnabled')
            ->renderAsSwitch()
            ->setHelp('Enable two-factor authentication');

        yield BooleanField::new('enabled')
            ->renderAsSwitch()
            ->setHelp('Enable/disable these credentials');

        yield SmartDateTimeField::new('createdAt')
            ->hideOnForm();

        yield SmartDateTimeField::new('updatedAt')
            ->hideOnForm();
    }
}
