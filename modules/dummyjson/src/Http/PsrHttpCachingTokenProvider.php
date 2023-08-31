<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Http;

use DigitalSilk\DummyJson\Codec\StreamingDecoderInterface;
use DigitalSilk\DummyJson\Codec\StreamingEncoderInterface;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * An authentication token provider that uses PSR-compatible client and cache.
 *
 * The PSR-18 client is used to retrieve the token from a remote API.
 * The PSR-16 cache is used to cache the token.
 */
class PsrHttpCachingTokenProvider implements AuthTokenProviderInterface
{
    protected ClientInterface $client;
    protected CacheInterface $cache;
    protected string $cacheKey;
    protected ?int $tokenTtl;
    protected UriInterface $endpointUri;
    protected RequestFactoryInterface $requestFactory;
    protected StreamingEncoderInterface $encoder;
    protected StreamingDecoderInterface $decoder;
    protected string $username;
    protected string $password;

    /**
     * @param ClientInterface $client The client to use for making requests.
     * @param CacheInterface $cache The cache to use for token persistence.
     * @param string $cacheKey The key that the token will have in cache.
     * @param int|null $tokenTtl The TTL for token validity, in seconds, if specified; otherwise, as long as possible.
     * @param UriInterface $endpointUri The URI of the authentication endpoint.
     * @param RequestFactoryInterface $requestFactory The factory used for making requests.
     * @param StreamingEncoderInterface $encoder Encodes request data.
     * @param StreamingDecoderInterface $decoder Decodes response data.
     * @param string $username The username to authenticate with.
     * @param string $password The password to use for authentication.
     */
    public function __construct(
        ClientInterface $client,
        CacheInterface $cache,
        string $cacheKey,
        ?int $tokenTtl,
        UriInterface $endpointUri,
        RequestFactoryInterface $requestFactory,
        StreamingEncoderInterface $encoder,
        StreamingDecoderInterface $decoder,
        string $username,
        string $password
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
        $this->tokenTtl = $tokenTtl;
        $this->endpointUri = $endpointUri;
        $this->requestFactory = $requestFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function provideAuthToken(): string
    {
        $cache = $this->cache;
        $cacheKey = $this->cacheKey;

        if (!$cache->has($cacheKey)) {
            $token = $this->retrieveToken($this->username, $this->password);
            $cache->set($cacheKey, $token, $this->tokenTtl);
        }

        return $cache->get($cacheKey);
    }

    /**
     * Retrieves an authentication token with the given credentials, bypassing cache.
     *
     * @param string $username The username to authenticate with.
     * @param string $password The password to use for authentication.
     *
     * @return string The token.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function retrieveToken(string $username, string $password): string
    {
        $uri = $this->endpointUri;
        $body = $this->encoder->encode([
            'username' => $username,
            'password' => $password,
            'expiresInMins' => ceil($this->tokenTtl / 60),
        ]);
        $request = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        try {
            $response = $this->client->sendRequest($request);
        } catch (Exception $e) {
            if (! $e instanceof RuntimeException) {
                $e = new RuntimeException('Could not retrieve token from API', 0, $e);
            }

            throw $e;
        }
        $responseCode = $response->getStatusCode();

        if ($responseCode === 400) {
            throw new ApiException(sprintf('Wrong credentials: tried username "%1$s"', $username));
        }

        if (!($responseCode >= 200 && $responseCode < 300) && $responseCode !== 302) {
            throw new UnexpectedValueException(
                sprintf('Server at "%1$s" responded with invalid code "%2$d"', (string) $uri, $responseCode)
            );
        }

        $data = $this->normalizeArray($this->decoder->decode($response->getBody()));
        if (!isset($data['token'])) {
            throw new UnexpectedValueException('Response does not contain token');
        }

        return (string) $data['token'];
    }

    /**
     * Normalizes any iterable to an array.
     *
     * @param iterable $iterable The iterable to normalize.
     *
     * @return array The normalized array.
     *
     * @throws RuntimeException If problem normalizing.
     */
    protected function normalizeArray(iterable $iterable): array
    {
        if (!is_array($iterable)) {
            $iterable = iterator_to_array($iterable);
        }

        return $iterable;
    }
}
