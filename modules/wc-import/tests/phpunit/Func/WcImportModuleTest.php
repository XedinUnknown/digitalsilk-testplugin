<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Test\Func;

use DigitalSilk\TestPlugin\Test\AbstractModularTestCase;
use DigitalSilk\WcImport\WcImportModule as Subject;

class WcImportModuleTest extends AbstractModularTestCase
{
    public function testBootstrap()
    {
        $this->expectNotToPerformAssertions();
        $container = $this->bootstrapModules([new Subject()], []);
    }
}
