<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

use DigitalSilk\DummyJson\Data\ProductInterface;
use RuntimeException;
use WC_Product;

/**
 * Represents something that can import a single product.
 */
interface ProductImporterInterface
{
    /**
     * Imports a product.
     *
     * @param ProductInterface $product The product to import.
     *
     * @return WC_Product The imported product.
     *
     * @throws RuntimeException If problem importing.
     */
    public function importProduct(ProductInterface $product): WC_Product;
}
