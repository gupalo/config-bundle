<?php

declare(strict_types=1);

namespace Gupalo\ConfigBundle\Controller;

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

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name');
        yield TextareaField::new('value')
            ->setNumOfRows(10)
            ->setRequired(false);
    }
}
