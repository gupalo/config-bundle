<?php

declare(strict_types=1);

namespace Gupalo\ConfigBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Gupalo\ConfigBundle\Entity\Config;

/**
 * EasyAdmin CRUD for the {@see Config} entity. Consumers wire it into their dashboard with:
 *
 *     yield MenuItem::linkTo(ConfigCrudController::class, 'Config', 'fas fa-gears');
 *
 * Field shape is fixed on purpose: this bundle's value of being a config table is that any
 * row can hold an arbitrary string. A per-row type-aware widget would be a different product.
 * Operators who want type-aware fields override this controller in their own app.
 *
 * Actions are added explicitly here (NEW + EDIT + DELETE on PAGE_INDEX, plus SAVE_AND_RETURN
 * on NEW/EDIT) rather than relying on dashboard defaults — the EasyAdmin 5 dashboard's
 * `configureActions()` does add those, but a consumer that swaps the dashboard controller
 * (or wraps the helper bundle's `CrudControllerTrait`) can lose them silently, so we make
 * this controller self-sufficient.
 *
 * @extends AbstractCrudController<Config>
 */
class ConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Config::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Config')
            ->setEntityLabelInPlural('Config')
            ->setSearchFields(['name', 'value']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('value');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name');
        yield TextareaField::new('value')
            ->setNumOfRows(10)
            ->setRequired(false);
    }
}
