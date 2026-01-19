<?php

namespace Gupalo\ConfigBundle\Tests\Entity;

use Gupalo\ConfigBundle\Entity\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $config = new Config()
            ->setName('test')
            ->setValue('test_value');

        $this->assertSame('test', $config->getName());
        $this->assertSame('test_value', $config->getValue());
    }

    public function testIdIsNullForNewEntity(): void
    {
        $config = new Config();

        $this->assertNull($config->getId());
    }

    public function testSetNameWithNull(): void
    {
        $config = new Config()->setName(null);

        $this->assertSame('', $config->getName());
    }

    public function testSetNameTruncatesLongString(): void
    {
        $longName = str_repeat('a', 300);
        $config = new Config()->setName($longName);

        $this->assertSame(255, mb_strlen($config->getName()));
        $this->assertSame(str_repeat('a', 255), $config->getName());
    }

    public function testSetValueWithNull(): void
    {
        $config = new Config()->setValue(null);

        $this->assertNull($config->getValue());
    }

    public function testSetValueWithEmptyString(): void
    {
        $config = new Config()->setValue('');

        $this->assertSame('', $config->getValue());
    }

    public function testFluentInterface(): void
    {
        $config = new Config();

        $this->assertSame($config, $config->setName('test'));
        $this->assertSame($config, $config->setValue('value'));
    }

    public function testDefaultValues(): void
    {
        $config = new Config();

        $this->assertSame('', $config->getName());
        $this->assertSame('', $config->getValue());
    }
}
