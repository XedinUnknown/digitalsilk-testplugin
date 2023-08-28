<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Http;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * Represents a client that can send requests with data to API endpoints.
 */
interface ApiClientInterface
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    /**
     * Send a request to an API endpoint.
     *
     * @param string $url The endpoint URL, relative to API basepath.
     * @param string $method The request method.
     * @param ?iterable $data The data to optionally send in the request.
     *
     * @return DataResponseInterface The response from the server.
     * @throws ClientExceptionInterface If problem sending.
     * @throws ApiExceptionInterface If API reports a problem.
     * @throws RuntimeException For any other problem in response handling.
     */
    public function sendRequest(string $url, string $method, ?iterable $data = null): DataResponseInterface;
}
