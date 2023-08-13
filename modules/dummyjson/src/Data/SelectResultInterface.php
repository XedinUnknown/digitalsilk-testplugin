<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use Traversable;

/**
 * Represents a result of a `SELECT` query.
 *
 * @template-covariant TValue
 * @extends Traversable<array-key, TValue>
 */
interface SelectResultInterface extends Traversable
{
    /**
     * Retrieves the number of rows in the result, ignoring pagination.
     *
     * @return int The total number of rows found.
     */
    public function getFoundRowsCount(): int;
}
