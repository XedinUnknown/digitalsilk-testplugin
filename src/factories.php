<?php

declare(strict_types=1);

use Dhii\Package\Version\StringVersionFactoryInterface;
use Dhii\Services\Factories\Alias;
use Dhii\Services\Factories\GlobalVar;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Dhii\Versions\StringVersionFactory;
use DigitalSilk\TestPlugin\FilePathPluginFactory;
use WpOop\WordPress\Plugin\FilePathPluginFactoryInterface;
use WpOop\WordPress\Plugin\PluginInterface;

return function (): array {
    return [
        'digitalsilk/testplugin/is_debug' => new Value(WP_DEBUG),
        'digitalsilk/testplugin/execution_timeout' => new Value(ini_get('max_execution_time')),
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
        'wp/core/wpdb' => new GlobalVar('wpdb'),
        'digitalsilk/testplugin/version_factory' => new Alias('package/version_factory'),
        'package/version_factory' => new Factory([
        ], function () {
            return new StringVersionFactory();
        }),

        #####################################################
        # Module Wiring
        #####################################################
        'digitalsilk/dummyjson/wpdb' => new Alias('wp/core/wpdb'),
        'digitalsilk/dummyjson/is_debug' => new Alias('digitalsilk/testplugin/is_debug'),
        'digitalsilk/dummyjson/api/auth/username' => new Alias('digitalsilk/wc-import/dummyjson/username'),
        'digitalsilk/dummyjson/api/auth/password' => new Alias('digitalsilk/wc-import/dummyjson/password'),

        'digitalsilk/wc-import/is_debug' => new Alias('digitalsilk/testplugin/is_debug'),
        'digitalsilk/wc-import/list_products_command' => new Alias('digitalsilk/dummyjson/api/command/products/list'),
        'digitalsilk/wc-import/product_execution_timeout' => new Alias('digitalsilk/testplugin/execution_timeout'),
    ];
};
