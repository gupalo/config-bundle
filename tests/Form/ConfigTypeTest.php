<?php

namespace Gupalo\ConfigBundle\Tests\Form;

use Gupalo\ConfigBundle\Entity\Config;
use Gupalo\ConfigBundle\Form\ConfigType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

class ConfigTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'test_config',
            'value' => 'test_value',
        ];

        $model = new Config();
        $form = $this->factory->create(ConfigType::class, $model);

        $expected = new Config()
            ->setName('test_config')
            ->setValue('test_value');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getName(), $model->getName());
        $this->assertEquals($expected->getValue(), $model->getValue());
    }

    public function testFormHasExpectedFields(): void
    {
        $form = $this->factory->create(ConfigType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('value'));
        $this->assertTrue($form->has('save'));
    }

    public function testFormFieldTypes(): void
    {
        $form = $this->factory->create(ConfigType::class);

        $this->assertInstanceOf(TextType::class, $form->get('name')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(TextareaType::class, $form->get('value')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(SubmitType::class, $form->get('save')->getConfig()->getType()->getInnerType());
    }

    public function testFormDataClass(): void
    {
        $form = $this->factory->create(ConfigType::class);

        $this->assertSame(Config::class, $form->getConfig()->getOption('data_class'));
    }
}
