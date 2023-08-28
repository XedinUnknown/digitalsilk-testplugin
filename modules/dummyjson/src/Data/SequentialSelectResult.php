<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use Generator;
use IteratorAggregate;

/**
 * A select result that yields non-scalar values, reading total from scalar ones.
 *
 * Raw data members from an iterable need to be separated into "items" and metadata (total, in this case).
 * Due to the sequential nature of iterable data representations, which are necessary to facilitate streaming
 * behaviour, data members that physically appear towards the end of the stream content can only be read
 * after the content before has been processed. Additionally, decoding with JSON pointers "flattens" results matching
 * multiple pointers in a way that they are indistinguishable from each other: string keys are removed, and all
 * are renumbered. One of the ways to retrieve only the required data is to depend on the type of the value itself.
 * In this scenario it's possible, because `/products` is a list of maps, and `/total` is scalar.
 */
class SequentialSelectResult implements IteratorAggregate, SelectResultInterface
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
            if (is_scalar($value)) {
                $this->total = intval($value);
            }
            else {
                yield $value;
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
