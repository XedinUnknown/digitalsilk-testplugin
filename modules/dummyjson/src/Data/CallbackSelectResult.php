<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use DigitalSilk\DummyJson\CallbackIterator;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

/**
 * A select result decorator that applies a callback to each element.
 *
 * @template In
 * @template Out
 */
class CallbackSelectResult implements SelectResultInterface, IteratorAggregate
{
    protected SelectResultInterface $selectResult;
    /** @var callable(In, mixed, iterable): Out */
    protected $callback;

    /**
     * @param SelectResultInterface $selectResult
     * @param callable(In, mixed, iterable): Out $callback
     */
    public function __construct(
        SelectResultInterface $selectResult,
        callable $callback
    ) {
        $this->selectResult = $selectResult;
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function getFoundRowsCount(): ?int
    {
        return $this->selectResult->getFoundRowsCount();
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new CallbackIterator(new IteratorIterator($this->selectResult), $this->callback);
    }
}
