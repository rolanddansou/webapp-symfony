<?php

namespace App\Controller\Admin;

use App\Entity\Access\AdminUser;
use App\Feature\Helper\Timezones;
use App\Feature\Shared\Domain\IRoleManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\ExpressionLanguage\Expression;

abstract class SharedAbstractCrudController extends AbstractCrudController
{
    public function __construct(
        protected readonly IRoleManager $roleManager,
        protected readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    /**
     * Configure les permissions par défaut pour toutes les actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions->setPermissions([
            Action::BATCH_DELETE => AdminUser::ROLE_ADMIN,
            Action::DELETE => AdminUser::ROLE_ADMIN,
            Action::DETAIL => AdminUser::ROLE_ADMIN,
            Action::EDIT => AdminUser::ROLE_ADMIN,
            Action::INDEX => AdminUser::ROLE_ADMIN,
            Action::NEW => AdminUser::ROLE_ADMIN,
            Action::SAVE_AND_ADD_ANOTHER => AdminUser::ROLE_ADMIN,
            Action::SAVE_AND_CONTINUE => AdminUser::ROLE_ADMIN,
            Action::SAVE_AND_RETURN => AdminUser::ROLE_ADMIN,
        ]);
    }

    /**
     * Active l'action BATCH_DELETE avec une permission personnalisée
     */
    public function allowBatchDelete(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::BATCH_DELETE, $permission);

        return $parentActions;
    }

    /**
     * Active l'action DELETE avec une permission personnalisée
     */
    public function allowDelete(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::DELETE, $permission);

        return $parentActions;
    }

    /**
     * Active l'action DETAIL avec une permission personnalisée
     */
    public function allowDetail(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::DETAIL, $permission);

        return $parentActions;
    }

    /**
     * Active l'action EDIT avec une permission personnalisée
     */
    public function allowEdit(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::EDIT, $permission);

        return $parentActions;
    }

    /**
     * Active l'action INDEX avec une permission personnalisée
     */
    public function allowIndex(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::INDEX, $permission);

        return $parentActions;
    }

    /**
     * Active l'action NEW avec une permission personnalisée
     */
    public function allowNew(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::NEW , $permission);

        return $parentActions;
    }

    /**
     * Active l'action SAVE_AND_ADD_ANOTHER avec une permission personnalisée
     */
    public function allowSaveAndAddAnother(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::SAVE_AND_ADD_ANOTHER, $permission);

        return $parentActions;
    }

    /**
     * Active l'action SAVE_AND_CONTINUE avec une permission personnalisée
     */
    public function allowSaveAndContinue(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::SAVE_AND_CONTINUE, $permission);

        return $parentActions;
    }

    /**
     * Active l'action SAVE avec une permission personnalisée
     */
    public function allowSave(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions = $this->allowSaveAndContinue($parentActions, $permission);
        $parentActions = $this->allowSaveAndAddAnother($parentActions, $permission);

        return $this->allowSaveAndReturn($parentActions, $permission);
    }

    /**
     * Active l'action SAVE_AND_RETURN avec une permission personnalisée
     */
    public function allowSaveAndReturn(Actions $actions, string|Expression $permission = ""): Actions
    {
        $parentActions = parent::configureActions($actions);

        if (empty($permission)) {
            $permission = AdminUser::ROLE_ADMIN;
        }

        $parentActions->setPermission(Action::SAVE_AND_RETURN, $permission);

        return $parentActions;
    }

    /**
     * Désactive la suppression par lot
     */
    public function disableBatchDelete(Actions $actions): Actions
    {
        return $actions->disable(Action::BATCH_DELETE);
    }

    /**
     * Configure les permissions pour une action spécifique
     */
    public function setActionPermission(
        Actions $actions,
        string $actionName,
        string|Expression $permission
    ): Actions {
        return $actions->setPermission($actionName, $permission);
    }

    /**
     * Configure les permissions pour plusieurs actions
     */
    public function setActionsPermissions(Actions $actions, array $permissions): Actions
    {
        foreach ($permissions as $actionName => $permission) {
            $actions->setPermission($actionName, $permission);
        }

        return $actions;
    }

    /**
     * Désactive plusieurs actions
     */
    public function disableActions(Actions $actions, array $actionNames): Actions
    {
        foreach ($actionNames as $actionName) {
            $actions->disable($actionName);
        }

        return $actions;
    }

    /**
     * Active le mode lecture seule (désactive NEW, EDIT, DELETE, BATCH_DELETE)
     */
    public function enableReadOnlyMode(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE);
    }

    /**
     * Configure les actions pour un rôle éditeur (peut créer et modifier mais pas supprimer)
     */
    public function configureEditorActions(Actions $actions, string $editorRole): Actions
    {
        return $actions->setPermissions([
            Action::INDEX => $editorRole,
            Action::DETAIL => $editorRole,
            Action::NEW => $editorRole,
            Action::EDIT => $editorRole,
            Action::SAVE_AND_ADD_ANOTHER => $editorRole,
            Action::SAVE_AND_CONTINUE => $editorRole,
            Action::SAVE_AND_RETURN => $editorRole,
            Action::DELETE => AdminUser::ROLE_ADMIN,
            Action::BATCH_DELETE => AdminUser::ROLE_ADMIN,
        ]);
    }

    /**
     * Configure les actions pour un rôle lecteur (consultation uniquement)
     */
    public function configureViewerActions(Actions $actions, string $viewerRole): Actions
    {
        return $actions
            ->setPermission(Action::INDEX, $viewerRole)
            ->setPermission(Action::DETAIL, $viewerRole)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    protected function hasRole(string $role): bool
    {
        return $this->roleManager->hasRole($role);
    }

    /**
     * Vérifie si l'utilisateur a au moins un des rôles spécifiés
     */
    protected function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Configuration CRUD par défaut
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setTimezone(Timezones::AFRICA_PORTO_NOVO)
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->setDateFormat('dd/MM/yyyy')
            ->setTimeFormat('HH:mm')
            ->setNumberFormat('%.2f')
            ->setPaginatorPageSize(20)
            ->setPaginatorRangeSize(4)
            ->setDefaultSort(['id' => 'DESC']);
    }

    /**
     * Configuration des filtres par défaut
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    /**
     * Crée une expression de permission basée sur plusieurs rôles (OR)
     */
    protected function createRoleExpression(array $roles): Expression
    {
        $roleChecks = array_map(
            fn($role) => sprintf("is_granted('%s')", $role),
            $roles
        );

        return new Expression(implode(' or ', $roleChecks));
    }

    /**
     * Crée une expression de permission nécessitant tous les rôles (AND)
     */
    protected function createRequireAllRolesExpression(array $roles): Expression
    {
        $roleChecks = array_map(
            fn($role) => sprintf("is_granted('%s')", $role),
            $roles
        );

        return new Expression(implode(' and ', $roleChecks));
    }
}
