<?php

declare(strict_types=1);

use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use DigitalSilk\WcImport\ProductImporter;
use DigitalSilk\WcImport\ProductImporterInterface;

return function (string $modDir): array {
    return [
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
    ];
};
