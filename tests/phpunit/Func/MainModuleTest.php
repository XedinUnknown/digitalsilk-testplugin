<?php

declare(strict_types=1);

namespace DigitalSilk\TestPlugin\Test\Func;

use Dhii\Services\Factories\Value;
use DigitalSilk\TestPlugin\Plugin;
use DigitalSilk\TestPlugin\Test\PluginFunctionMocks;
use Psr\Container\ContainerInterface;
use DigitalSilk\TestPlugin\MainModule as Subject;
use DigitalSilk\TestPlugin\Test\AbstractModularTestCase;

use function Brain\Monkey\Functions\when;

class MainModuleTest extends AbstractModularTestCase
{
    use PluginFunctionMocks;

    public function testMainModuleLoads()
    {
        $this->mockPluginFunctions();
        $appContainer = $this->bootstrapModules([new Subject(BASE_PATH, BASE_DIR)],[
            'me/plugin/main_file_path' => new Value(BASE_PATH),
        ]);
        $this->assertInstanceOf(ContainerInterface::class, $appContainer);
        $this->assertInstanceOf(Plugin::class, $appContainer->get('me/plugin/plugin'));
        $this->assertFalse($appContainer->has(uniqid('non-existing-service')));
    }
}
