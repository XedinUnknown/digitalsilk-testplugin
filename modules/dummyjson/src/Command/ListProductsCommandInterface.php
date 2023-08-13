<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Command;

use DigitalSilk\DummyJson\Data\ProductInterface;
use DigitalSilk\DummyJson\Data\SelectResultInterface;

/**
 * Represents a command that retrieves a list of products.
 */
interface ListProductsCommandInterface
{
    /**
     * Retrieves a paginated list of products, optionally filtered by keyphrase.
     *
     * @param ?string $keyphrase They keyphrase, if any, to filter the product list by.
     * @param int $limit The maximal amount of products to retrieve.
     * @param int $offset The number of products to skip from the start.
     *
     * @return SelectResultInterface<ProductInterface> The list of products.
     *
     * @throw RuntimeException If problem retrieving.
     */
    public function listProducts(?string $keyphrase, int $limit = 0, int $offset = 0): SelectResultInterface;
}
