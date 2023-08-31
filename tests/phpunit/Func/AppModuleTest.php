<?php

declare(strict_types=1);

namespace DigitalSilk\TestPlugin\Test\Func;

use DigitalSilk\TestPlugin\Test\PluginFunctionMocks;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use DigitalSilk\TestPlugin\Test\AbstractApplicationTestCase;
use wpdb;

class AppModuleTest extends AbstractApplicationTestCase
{
    use PluginFunctionMocks;

    public function testInitializationAndExtension()
    {
        {
            $this->mockPluginFunctions();
            $serviceName = uniqid('serviceName');
            $factoryValue = uniqid('serviceValue');
            $extensionValue = uniqid('extensionValue');
            $valueSeparator = '/';

            $container = $this->bootstrapApplication(
                [
                    'digitalsilk/testplugin/main_file_path' => function () {
                        return BASE_PATH;
                    },
                    'digitalsilk/testplugin/basedir' => function () {
                        return BASE_DIR;
                    },
                    'wp/core/abspath' => function () {
                        return ABSPATH;
                    },
                    'wp/core/wpdb' => function () {
                        var_dump(__METHOD__);
                        return $this->createWpdb();
                    },

                    $serviceName => function () use ($factoryValue): string {
                        return $factoryValue;
                    },
                ],
                [
                    $serviceName => function (ContainerInterface $c, string $prev) use ($extensionValue, $valueSeparator): string {
                        return "{$prev}{$valueSeparator}{$extensionValue}";
                    },
                ]
            );
        }

        {
            $serviceValue = $container->get($serviceName);
            $this->assertEquals("{$factoryValue}{$valueSeparator}{$extensionValue}", $serviceValue);
        }
    }

    /**
     * @return wpdb&MockObject
     */
    protected function createWpdb(): wpdb
    {
        $mock = $this->getMockBuilder(wpdb::class)
            ->getMock();

        return $mock;
    }
}
