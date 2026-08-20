<?php

declare(strict_types=1);

namespace Gupalo\ConfigBundle\Tests\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Gupalo\ConfigBundle\Controller\ConfigCrudController;
use Gupalo\ConfigBundle\Entity\Config;
use PHPUnit\Framework\TestCase;

/**
 * Pure-unit smoke test for the CrudController field shape. The bundle's phpunit.xml.dist
 * does not boot a Symfony kernel, so we instantiate the controller directly and assert on
 * the fields it yields — enough to catch an accidental rename or field-shape change.
 *
 * onlyOnIndex()/onlyOnForms() are render-time flags, not yield-time filters, so we do
 * not assert the per-page visibility here. Filters/Crud configuration are EasyAdmin's own
 * machinery; trusting the EasyAdmin tests to cover those is the simplest read.
 */
class ConfigCrudControllerTest extends TestCase
{
    public function testItManagesTheConfigEntity(): void
    {
        self::assertSame(Config::class, ConfigCrudController::getEntityFqcn());
    }

    public function testIndexPageYieldsIdNameAndValue(): void
    {
        $controller = new ConfigCrudController();

        $fields = iterator_to_array($controller->configureFields('index'), false);

        self::assertCount(3, $fields);
        self::assertInstanceOf(IdField::class, $fields[0]);
        self::assertInstanceOf(TextField::class, $fields[1]);
        self::assertInstanceOf(TextareaField::class, $fields[2]);
    }
}
