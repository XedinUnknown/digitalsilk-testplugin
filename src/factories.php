<?php

declare(strict_types=1);

use Dhii\Package\Version\StringVersionFactoryInterface;
use Dhii\Services\Factories\Alias;
use Dhii\Services\Factory;
use Dhii\Versions\StringVersionFactory;
use DigitalSilk\TestPlugin\FilePathPluginFactory;
use WpOop\WordPress\Plugin\FilePathPluginFactoryInterface;
use WpOop\WordPress\Plugin\PluginInterface;

return function (): array {
    return [
        'digitalsilk/testplugin/plugin' => new Factory([
            'wordpress/plugin_factory',
            'digitalsilk/testplugin/main_file_path',
        ], function (FilePathPluginFactoryInterface $factory, string $mainFile): PluginInterface {
            return $factory->createPluginFromFilePath($mainFile);
        }),
        'digitalsilk/testplugin/plugin_factory'  => new Alias('wordpress/plugin_factory'),
        'wordpress/plugin_factory' => new Factory([
            'package/version_factory',
        ], function (StringVersionFactoryInterface $factory): FilePathPluginFactoryInterface {
            return new FilePathPluginFactory($factory);
        }),
        'digitalsilk/testplugin/version_factory' => new Alias('package/version_factory'),
        'package/version_factory' => new Factory([
        ], function () {
            return new StringVersionFactory();
        }),

        #####################################################
        # Module Wiring
        #####################################################
    ];
};
