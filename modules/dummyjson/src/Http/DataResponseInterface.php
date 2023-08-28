<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Http;

use Psr\Http\Message\ResponseInterface;

interface DataResponseInterface extends ResponseInterface
{
    /**
     * Retrieves the data associated with this instance.
     *
     * @return ?iterable The data.
     */
    public function getData(): ?iterable;

    /**
     * Assigns data to a new instance.
     *
     * @param iterable $data The data to assign.
     *
     * @return static A new instance with the configured data.
     */
    public function withData(iterable $data): self;
}
