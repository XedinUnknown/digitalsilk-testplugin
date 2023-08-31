<?php

declare(strict_types=1);

use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use DigitalSilk\WcImport\CustomContextLogger;
use DigitalSilk\WcImport\Hooks\AddNavigation;
use DigitalSilk\WcImport\Hooks\AddTaxonomy;
use DigitalSilk\WcImport\Hooks\RenderSettingsPage;
use DigitalSilk\WcImport\Hooks\RunImport;
use DigitalSilk\WcImport\Hooks\SaveSettings;
use DigitalSilk\WcImport\Hooks\ScheduleImport;
use DigitalSilk\WcImport\ProductImporter;
use DigitalSilk\WcImport\ProductImporterInterface;
use Psr\Log\LoggerInterface;

return function (string $modDir): array {
    return [
        'digitalsilk/wc-import/is_debug' => new Value(false),
        'digitalsilk/wc-import/batch_size' => new Value(3),
        'digitalsilk/wc-import/logging/import_log_name' => new Value('digitalsilk-wc-import'),
        'digitalsilk/wc-import/logging/import_logger' => new Factory([
            'digitalsilk/wc-import/logging/import_log_name',
        ], function (string $logName): LoggerInterface {
            return new CustomContextLogger([
                'source' => $logName,
            ]);
        }),
        'digitalsilk/wc-import/importer/product' => new Factory([
            'digitalsilk/wc-import/taxonomy/brand/name',
        ], function (string $brandTaxonomyName): ProductImporterInterface {
            return new ProductImporter('product_cat', $brandTaxonomyName);
        }),
        'digitalsilk/wc-import/taxonomy/brand/name' => new Value('brand'),
        'digitalsilk/wc-import/taxonomy/brand/settings' => new Value([
            'labels' => [
                'name' => __('Brands', ),
                'singular_name' => __('Brand'),
            ],
            'hierarchical' => true,
            'public' => true,
        ]),
        'digitalsilk/wc-import/dummyjson/username' => new Value(''),
        'digitalsilk/wc-import/dummyjson/password' => new Value(''),
        'digitalsilk/wc-import/hooks/save_settings' => new Constructor(SaveSettings::class),
        'digitalsilk/wc-import/hooks/render_settings_page' => new Constructor(RenderSettingsPage::class, [
            'digitalsilk/wc-import/dummyjson/username',
            'digitalsilk/wc-import/dummyjson/password',
        ]),
        'digitalsilk/wc-import/hooks/add_navigation' => new Factory([
            'digitalsilk/wc-import/hooks/render_settings_page',
        ], function (RenderSettingsPage $renderSettingsPageHook) {
            $pageTitle = 'DummyJSON';
            $pageSlug = 'dummyjson';
            return new AddNavigation($renderSettingsPageHook, $pageTitle, $pageSlug);
        }),
        'digitalsilk/wc-import/hooks/schedule_immediate_import' => new Constructor(ScheduleImport::class),
        'digitalsilk/wc-import/hooks/run_import' => new Constructor(RunImport::class, [
            'digitalsilk/wc-import/is_debug',
            'digitalsilk/dummyjson/api/command/products/list',
            'digitalsilk/wc-import/importer/product',
            'digitalsilk/wc-import/batch_size',
            'digitalsilk/wc-import/logging/import_logger',
            'digitalsilk/wc-import/hooks/schedule_immediate_import',
        ]),
        'digitalsilk/wc-import/hooks/add_brand_taxonomy' => new Factory([
            'digitalsilk/wc-import/taxonomy/brand/name',
            'digitalsilk/wc-import/taxonomy/brand/settings',
        ], function (string $name, array $args): AddTaxonomy {
            return new AddTaxonomy($name, $args, 'product');
        }),
    ];
};
