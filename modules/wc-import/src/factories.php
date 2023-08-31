<?php

declare(strict_types=1);

use Dhii\Services\Factory;
use DigitalSilk\WcImport\ProductImporter;
use DigitalSilk\WcImport\ProductImporterInterface;

return function (string $modDir): array {
    return [
        'digitalsilk/wc-import/importer/product' => new Factory([], function (): ProductImporterInterface {
            return new ProductImporter();
        }),
    ];
};
