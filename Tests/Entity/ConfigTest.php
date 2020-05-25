<?php

namespace Gupalo\ConfigBundle\Tests\Entity;

use Gupalo\ConfigBundle\Entity\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetters(): void
    {
        $config = (new Config())
            ->setName('test')
            ->setValue('test_value');

        $this->assertSame('test', $config->getName());
        $this->assertSame('test_value', $config->getValue());
    }
}
