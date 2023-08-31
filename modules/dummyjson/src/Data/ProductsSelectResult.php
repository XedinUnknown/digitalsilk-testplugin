<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use Generator;
use IteratorAggregate;

/**
 * A select result that yields products from `/products` key, and remembers the `/total`.
 */
class ProductsSelectResult implements IteratorAggregate, SelectResultInterface
{
    protected iterable $data;
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected ?int $total;

    public function __construct(iterable $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Generator
    {
        foreach ($this->data as $key => $value) {
            if ($key === 'total') {
                $this->total = intval($value);
                continue;
            }

            if ($key === 'products') {
                yield from $value;
                continue;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getFoundRowsCount(): ?int
    {
        return $this->total;
    }
}
