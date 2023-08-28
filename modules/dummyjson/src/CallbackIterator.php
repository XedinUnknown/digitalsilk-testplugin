<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson;

use Iterator;
use UnexpectedValueException;

/**
 * @template TKey
 * @template-covariant TValue
 * @template OGValue
 * @implements Iterator<TKey, TValue>
 */
class CallbackIterator implements Iterator
{
    /** @var Iterator<TKey, OGValue> */
    protected Iterator $innerIterator;
    /** @var ?TValue */
    protected $current = null;
    /** @var callable(OGValue, TKey, static): TValue */
    protected $callback;

    /**
     * @param Iterator<TKey, OGValue> $innerIterator
     * @param callable(OGValue, TKey, static): TValue $callback
     */
    public function __construct(Iterator $innerIterator, callable $callback)
    {
        $this->innerIterator = $innerIterator;
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     *
     * @return TValue The current value.
     *
     * @throws UnexpectedValueException If problem getting current item from inner iterator.
     */
    public function current()
    {
        if ($this->current === null) {
            $innerCurrent = $this->getInnerIterator()->current();
            if ($innerCurrent === null) {
                throw new UnexpectedValueException('Inner iterator returned invalid current item');
            }

            $callback = $this->callback;
            $this->current = $callback($innerCurrent, $this->key(), $this);
        }

        return $this->current;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        // Clear current cache
        if ($this->current !== null) {
            $this->current = null;
        }

        $this->getInnerIterator()->next();
    }

    /**
     * @inheritDoc
     *
     * @return TKey The current key.
     * @psalm-suppress MissingReturnType
     */
    public function key()
    {
        return $this->getInnerIterator()->key();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->getInnerIterator()->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $inner = $this->getInnerIterator();
        $inner->rewind();
    }

    /**
     * Retrieves the inner iterator.
     *
     * @return Iterator<TKey, OGValue> The inner iterator.
     */
    protected function getInnerIterator(): Iterator
    {
        return $this->innerIterator;
    }
}
