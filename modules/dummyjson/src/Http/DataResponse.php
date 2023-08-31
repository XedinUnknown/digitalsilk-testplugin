<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A response that includes decoded data.
 */
class DataResponse implements DataResponseInterface
{
    protected ResponseInterface $response;
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected ?iterable $data;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getData(): ?iterable
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function withData(iterable $data): DataResponseInterface
    {
        $me = clone $this;
        $me->data = $data;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion(string $version)
    {
        $response = $this->response->withProtocolVersion($version);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name)
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name)
    {
        return $this->response->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name)
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value)
    {
        $response = $this->response->withHeader($name, $value);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value)
    {
        $response = $this->response->withAddedHeader($name, $value);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name)
    {
        $response = $this->response->withoutHeader($name);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $response = $this->response->withBody($body);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @inheritDoc
     */
    public function withStatus(int $code, string $reasonPhrase = '')
    {
        $response = $this->response->withStatus($code, $reasonPhrase);
        $me = clone $this;
        $me->response = $response;

        return $me;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }
}
