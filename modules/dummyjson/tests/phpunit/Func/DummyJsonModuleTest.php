<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func;

use DigitalSilk\TestPlugin\Test\AbstractModularTestCase;
use DigitalSilk\DummyJson\DummyJsonModule as Subject;

class DummyJsonModuleTest extends AbstractModularTestCase
{
    public function testBootstrap()
    {
        $this->expectNotToPerformAssertions();
        $container = $this->bootstrapModules([new Subject()], []);
    }
}
