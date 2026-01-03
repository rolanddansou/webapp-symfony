<?php

namespace App\Controller\Admin;

use App\Entity\System\ContactMessage;
use App\Entity\System\Enum\ContactMessageStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactMessageCrudController extends SharedAbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message de Contact')
            ->setEntityLabelInPlural('Messages de Contact')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $parentActions = parent::configureActions($actions);
        return $parentActions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TextField::new('subject', 'Sujet');
        yield TextareaField::new('message', 'Message')->hideOnIndex();

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Nouveau' => ContactMessageStatus::NEW ,
                'Lu' => ContactMessageStatus::READ,
                'Répondu' => ContactMessageStatus::REPLIED,
            ])
            ->renderAsBadges([
                ContactMessageStatus::NEW->value => "danger" ,
                ContactMessageStatus::READ->value => "info",
                ContactMessageStatus::REPLIED->value => "success",
            ]);

        yield DateTimeField::new('createdAt', 'Reçu le')->hideOnForm();
    }
}
