<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Transform;

use RuntimeException;

/**
 * Something that can transform one data type into another.
 *
 * @psalm-immutable
 *
 * @psalm-template-covariant Out
 * @psalm-template-covariant In
 */
interface TransformerInterface
{
    /**
     * Transforms the provided value.
     *
     * @param In $value The value to transform.
     *
     * @return Out The result of the transformation.
     *
     * @throws RuntimeException If problem transforming.
     */
    public function transform($value);
}
