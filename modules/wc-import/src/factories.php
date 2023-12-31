<?php

declare(strict_types=1);

use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use DigitalSilk\DummyJson\Command\ListProductsCommandInterface;
use DigitalSilk\DummyJson\Data\ProductsSelectResult;
use DigitalSilk\DummyJson\Data\SelectResultInterface;
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
        'digitalsilk/wc-import/batch_size' => new Value(10),
        'digitalsilk/wc-import/import_limit' => new Value(0),
        'digitalsilk/wc-import/product_execution_timeout' => new Value(60),
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
                'name' => __('Brands',),
                'singular_name' => __('Brand'),
            ],
            'hierarchical' => true,
            'public' => true,
        ]),
        'digitalsilk/wc-import/dummyjson/username' => new Value(''),
        'digitalsilk/wc-import/dummyjson/password' => new Value(''),
        'digitalsilk/wc-import/hooks/save_settings' => new Constructor(SaveSettings::class),
        'digitalsilk/wc-import/hooks/render_settings_page' => new Factory(
            [
                'digitalsilk/wc-import/dummyjson/username',
                'digitalsilk/wc-import/dummyjson/password',
                'digitalsilk/wc-import/batch_size',
                'digitalsilk/wc-import/import_limit',
            ],
            /**
             * @param numeric $batchSize
             * @param numeric $importLimit
             */
            function (string $username, string $password, $batchSize, $importLimit) {
                $batchSize = intval($batchSize);
                $importLimit = intval($importLimit);
                return new RenderSettingsPage($username, $password, $batchSize, $importLimit);
            }
        ),
        'digitalsilk/wc-import/hooks/add_navigation' => new Factory([
            'digitalsilk/wc-import/hooks/render_settings_page',
        ], function (RenderSettingsPage $renderSettingsPageHook) {
            $pageTitle = 'DummyJSON';
            $pageSlug = 'dummyjson';
            return new AddNavigation($renderSettingsPageHook, $pageTitle, $pageSlug);
        }),
        'digitalsilk/wc-import/hooks/schedule_immediate_import' => new Constructor(ScheduleImport::class),
        'digitalsilk/wc-import/hooks/run_import' => new Factory(
            [
                'digitalsilk/wc-import/is_debug',
                'digitalsilk/wc-import/list_products_command',
                'digitalsilk/wc-import/importer/product',
                'digitalsilk/wc-import/batch_size',
                'digitalsilk/wc-import/import_limit',
                'digitalsilk/wc-import/logging/import_logger',
                'digitalsilk/wc-import/hooks/schedule_immediate_import',
                'digitalsilk/wc-import/product_execution_timeout',
            ],
            /**
             * @param numeric $batchSize
             * @param numeric $importLimit
             * @param numeric $productExecutionTimeout
             */
            function (
                bool $isDebug,
                ListProductsCommandInterface $listProductsCommand,
                ProductImporterInterface $productImporter,
                $batchSize,
                $importLimit,
                LoggerInterface $logger,
                ScheduleImport $scheduleImportHook,
                $productExecutionTimeout
            ): RunImport {
                $batchSize = intval($batchSize);
                $importLimit = intval($importLimit);

                $productExecutionTimeout = intval($productExecutionTimeout);
                if ($productExecutionTimeout < 0) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Execution timeout must be a non-negative integer; "%1$s" received',
                            $productExecutionTimeout
                        )
                    );
                }

                return new RunImport(
                    $isDebug,
                    $listProductsCommand,
                    $productImporter,
                    $batchSize,
                    $importLimit,
                    $logger,
                    $scheduleImportHook,
                    $productExecutionTimeout
                );
            }
        ),
        'digitalsilk/wc-import/hooks/add_brand_taxonomy' => new Factory([
            'digitalsilk/wc-import/taxonomy/brand/name',
            'digitalsilk/wc-import/taxonomy/brand/settings',
        ], function (string $name, array $args): AddTaxonomy {
            return new AddTaxonomy($name, $args, 'product');
        }),
        // No-op
        'digitalsilk/wc-import/list_products_command' => new Factory([], function (): ListProductsCommandInterface {
            return new class implements ListProductsCommandInterface {
                public function listProducts(
                    ?string $keyphrase = null,
                    int $limit = 0,
                    int $offset = 0
                ): SelectResultInterface {
                    return new ProductsSelectResult(['products' => [], 'total' => 0]);
                }
            };
        }),
    ];
};
