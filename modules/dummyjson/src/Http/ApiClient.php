<?php

namespace DigitalSilk\DummyJson\Http;

use ArrayIterator;
use DigitalSilk\DummyJson\Codec\StreamingDecoderInterface;
use DigitalSilk\DummyJson\Codec\StreamingEncoderInterface;
use Exception;
use IteratorIterator;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RangeException;
use RuntimeException;
use Traversable;

/**
 * A client that can send requests with data to API endpoints.
 */
class ApiClient implements ApiClientInterface
{
    protected UriInterface $baseUrl;
    protected ClientInterface $client;
    protected RequestFactoryInterface $requestFactory;
    protected UriFactoryInterface $uriFactory;
    protected StreamingEncoderInterface $encoder;
    protected StreamingDecoderInterface $decoder;

    public function __construct(
        UriInterface $baseUrl,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        StreamingEncoderInterface $encoder,
        StreamingDecoderInterface $decoder
    ) {
        $this->baseUrl = $baseUrl;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(string $url, string $method, ?iterable $data = null): DataResponseInterface
    {
        try {
            $fullUri = $this->resolveUri($this->baseUrl, $url);
            $request = $this->requestFactory->createRequest($method, $fullUri);

            $request = $method === 'GET'
                ? $this->encodeQueryData($request, $data)
                : $this->encodeBodyData($request, $data);
            $response = $this->client->sendRequest($request);
        } catch (Exception $e) {
            if (! $e instanceof ClientExceptionInterface) {
                $e = new ClientException($e->getMessage(), 0, $e);
            }

            throw $e;
        }

        $response = $this->decodeResponse($response);
        $this->validateResponse($response);

        return $response;
    }

    /**
     * Appends a relative URI to a base URI.
     *
     * @param UriInterface|string $baseUri The base URI to append to. Trailing slashes will be removed.
     * @param UriInterface|string $relUri The relative URI to append. Leading slashes will be removed.
     *
     * @return UriInterface A URI with relative URI appended to base via a path separator.
     */
    protected function resolveUri($baseUri, $relUri): UriInterface
    {
        $baseUri = rtrim((string) $baseUri, '/');
        $relUri = ltrim((string) $relUri, '/');
        $fullUri = $this->uriFactory->createUri("$baseUri/$relUri");

        return $fullUri;
    }

    /**
     * Configures the give request's body with the specified data, encoding it.
     *
     * @param RequestInterface $request The request to configure with data.
     * @param iterable|null $data The data to encode.
     *
     * @return RequestInterface The configured request.
     * @throws RuntimeException If problem configuring.
     */
    protected function encodeBodyData(RequestInterface $request, ?iterable $data): RequestInterface
    {
        if ($data !== null) {
            $body = $this->encoder->encode($data);
            $request = $request->withBody($body);
            $request = $request->withHeader('Content-Type', $this->encoder->getMimeType());
        }

        return $request;
    }

    /**
     * Configures the given request's URL with the given data, encoding it.
     *
     * @param RequestInterface $request The request to configure with data.
     * @param iterable|null $data The data to encode.
     *
     * @return RequestInterface The configured request.
     * @throws RuntimeException If problem configuring.
     */
    protected function encodeQueryData(RequestInterface $request, ?iterable $data): RequestInterface
    {
        if ($data !== null) {
            $url = $request->getUri();
            $query = $url->getQuery();
            parse_str($query, $queryData);

            foreach ($data as $key => $value) {
                $queryData[$key] = $value;
            }

            $query = http_build_query($queryData);
            $url = $url->withQuery($query);
            $request = $request->withUri($url);
        }

        return $request;
    }

    /**
     * Decodes the data in a response.
     *
     * @param ResponseInterface $response The response to decode.
     *
     * @return DataResponseInterface A response with decoded data.
     *
     * @throws RuntimeException If problem decoding.
     */
    protected function decodeResponse(ResponseInterface $response): DataResponseInterface
    {
        $decoder = $this->decoder;
        $allowedContentType = $decoder->getMimeType();

        if (!$response->hasHeader('Content-Type')) {
            throw new RangeException('Response does not specify a content type');
        }

        $contentType = $response->getHeader('Content-Type');
        $contentType = (string) array_pop($contentType);

        if ($contentType !== $allowedContentType) {
            throw new RangeException(sprintf('Unsupported content type "%1$s"', $contentType));
        }

        $data = $decoder->decode($response->getBody());
        $response = new DataResponse($response);
        $response = $response->withData($data);

        return $response;
    }

    /**
     * Validates a data response.
     *
     * The response body stream may be unbuffered, which means that it may only be consumed once.
     * For this reason, this method will only read the response data if its meta-data indicates that
     * it does not contain expected results, such as in an explicit error scenario.
     *
     * @param DataResponseInterface $response
     *
     * @return void
     *
     * @throws ApiExceptionInterface If not a valid API response.
     * @throws RuntimeException If problem validating.
     */
    protected function validateResponse(DataResponseInterface $response): void
    {
        $responseCode = $response->getStatusCode();
        if (!($responseCode >= 200 && $responseCode < 300) && $responseCode !== 302) {
            try {
                $data = $response->getData();
                $responseData = $data instanceof Traversable
                    ? iterator_to_array($data)
                    : $data;

            } catch (Exception $e) {
                throw new ApiException(
                    sprintf(
                        'API responded with an error HTTP code %1$d, but the response could not be decoded',
                        $responseCode
                    ),
                    0,
                    $e
                );
            }

            $errorMessage = isset($responseData['message'])
                ? (string) $responseData['message']
                : null;
            if (is_null($errorMessage)) {
                throw new ApiException(
                    sprintf(
                        'API responded with an error HTTP code %1$d, but the response did not contain a message',
                        $responseCode
                    )
                );
            }

            throw new ApiException(
                sprintf(
                    'API responded with an error code %1$d: %2$s',
                    $responseCode,
                    $errorMessage
                )
            );
        }
    }
}
