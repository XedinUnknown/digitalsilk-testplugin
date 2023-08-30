<?php

declare(strict_types=1);

use Dhii\Services\Factories\Alias;
use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\GlobalVar;
use Dhii\Services\Factories\StringService;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\Codec\JsonStreamingEncoder;
use DigitalSilk\DummyJson\Codec\StreamingDecoderInterface;
use DigitalSilk\DummyJson\Codec\StreamingEncoderInterface;
use DigitalSilk\DummyJson\Command\ApiListProductsCommand;
use DigitalSilk\DummyJson\Command\ListProductsCommandInterface;
use DigitalSilk\DummyJson\Http\ApiClient;
use DigitalSilk\DummyJson\Http\ApiClientInterface;
use DigitalSilk\DummyJson\Http\AuthTokenProviderInterface;
use DigitalSilk\DummyJson\Http\PsrHttpCachingTokenProvider;
use DigitalSilk\DummyJson\Transform\ProductHydrator;
use DigitalSilk\DummyJson\Transform\TransformerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use WpOop\HttpClient\Client;
use WpOop\TransientCache\CachePoolFactory;
use WpOop\TransientCache\CachePoolFactoryInterface;

return function (string $modDir): array {
    return [
        'digitalsilk/dummyjson/is_debug' => new Value(true),
        'digitalsilk/dummyjson/tempdir' => new Factory([], fn() => sys_get_temp_dir()),
        'digitalsilk/dummyjson/wpdb' => new GlobalVar('wpdb'),
        'digitalsilk/dummyjson/http/psr17_factory' => new Factory([], function (): Psr17Factory {
            $factory = new Psr17Factory();

            return $factory;
        }),
        'digitalsilk/dummyjson/http/request_factory' => new Alias('digitalsilk/dummyjson/http/psr17_factory'),
        'digitalsilk/dummyjson/http/uri_factory' => new Alias('digitalsilk/dummyjson/http/psr17_factory'),
        'digitalsilk/dummyjson/http/request_proxy_dir' => new Alias('digitalsilk/dummyjson/tempdir'),
        'digitalsilk/dummyjson/http/client' => new Factory([
            'digitalsilk/dummyjson/http/psr17_factory',
            'digitalsilk/dummyjson/http/request_proxy_dir',
        ], function (
            ResponseFactoryInterface $responseFactory,
            string $requestProxyDir
        ): ClientInterface {
            $httpClient = new Client([], $responseFactory, $requestProxyDir);

            return $httpClient;
        }),
        'digitalsilk/dummyjson/codec/json_streaming_encoder' => new Constructor(JsonStreamingEncoder::class, [
            'digitalsilk/dummyjson/is_debug',
        ]),
        'digitalsilk/dummyjson/cache/default_ttl' => new Value(0),
        'digitalsilk/dummyjson/cache/pool_factory' => new Constructor(CachePoolFactory::class, [
            'digitalsilk/dummyjson/wpdb',
        ]),
        'digitalsilk/dummyjson/api/encoder' => new Alias('digitalsilk/dummyjson/codec/json_streaming_encoder'),
        'digitalsilk/dummyjson/api/decoder' => new Factory([
            'digitalsilk/dummyjson/is_debug',
        ], function (bool $isDebug): StreamingDecoderInterface {
            return new JsonMachineDecoder($isDebug, ['']);
        }),
        'digitalsilk/dummyjson/api/cache/pool_name' => new Value('dummyjson/api'),
        'digitalsilk/dummyjson/api/cache/pool' => new Factory([
            'digitalsilk/dummyjson/cache/pool_factory',
            'digitalsilk/dummyjson/api/cache/pool_name',
        ], function (CachePoolFactoryInterface $factory, string $name): CacheInterface {
            return $factory->createCachePool($name);
        }),
        'digitalsilk/dummyjson/api/base_url' => new Value('https://dummyjson.com'),
        // Ensure different cache key per username+password combination
        'digitalsilk/dummyjson/api/auth/token_cache_key' => new Factory([
            'digitalsilk/dummyjson/api/auth/username',
            'digitalsilk/dummyjson/api/auth/password',
        ], function (
            string $username,
            string $password
        ) {
            $tokenName = sha1(implode('|', [$username, $password]));
            return sprintf('auth_token.%1$s', $tokenName);
        }),
        'digitalsilk/dummyjson/api/auth/token_ttl' => new Value(60 * 10), //  seconds
        'digitalsilk/dummyjson/api/auth/token_endpoint_url' => new StringService('{0}/auth/login', [
            'digitalsilk/dummyjson/api/base_url',
        ]),
        'digitalsilk/dummyjson/api/auth/username' => new Value(''),
        'digitalsilk/dummyjson/api/auth/password' => new Value(''),
        'digitalsilk/dummyjson/api/auth/encoder' => new Alias('digitalsilk/dummyjson/api/encoder'),
        'digitalsilk/dummyjson/api/auth/decoder' => new Alias('digitalsilk/dummyjson/api/decoder'),
        'digitalsilk/dummyjson/api/auth/token_provider' => new Factory([
            'digitalsilk/dummyjson/http/client',
            'digitalsilk/dummyjson/api/cache/pool',
            'digitalsilk/dummyjson/api/auth/token_cache_key',
            'digitalsilk/dummyjson/api/auth/token_ttl',
            'digitalsilk/dummyjson/api/auth/token_endpoint_url',
            'digitalsilk/dummyjson/http/uri_factory',
            'digitalsilk/dummyjson/http/request_factory',
            'digitalsilk/dummyjson/api/auth/encoder',
            'digitalsilk/dummyjson/api/auth/decoder',
            'digitalsilk/dummyjson/api/auth/username',
            'digitalsilk/dummyjson/api/auth/password',
        ], function (
            ClientInterface $client,
            CacheInterface $cache,
            string $cacheKey,
            int $tokenTtl,
            string $endpointUrl,
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamingEncoderInterface $encoder,
            StreamingDecoderInterface $decoder,
            string $username,
            string $password
        ): AuthTokenProviderInterface {
            $url = $uriFactory->createUri($endpointUrl);
            return new PsrHttpCachingTokenProvider(
                $client,
                $cache,
                $cacheKey,
                $tokenTtl,
                $url,
                $requestFactory,
                $encoder,
                $decoder,
                $username,
                $password
            );
        }),
        'digitalsilk/dummyjson/api/client' => new Factory([
            'digitalsilk/dummyjson/api/base_url',
            'digitalsilk/dummyjson/http/client',
            'digitalsilk/dummyjson/http/uri_factory',
            'digitalsilk/dummyjson/http/request_factory',
            'digitalsilk/dummyjson/api/encoder',
            'digitalsilk/dummyjson/api/decoder',
            'digitalsilk/dummyjson/api/auth/token_provider',
        ], function (
            string $baseUrl,
            ClientInterface $httpClient,
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamingEncoderInterface $encoder,
            StreamingDecoderInterface $decoder,
            ?AuthTokenProviderInterface $tokenProvider
        ): ApiClientInterface {
            $baseUrl = $uriFactory->createUri($baseUrl);

            $apiClient = new ApiClient(
                $baseUrl,
                $httpClient,
                $requestFactory,
                $uriFactory,
                $encoder,
                $decoder,
                $tokenProvider
            );

            return $apiClient;
        }),
        'digitalsilk/dummyjson/api/hydrator/product' => new Factory([
            'digitalsilk/dummyjson/http/uri_factory',
        ], function (UriFactoryInterface $uriFactory): TransformerInterface {
            return new ProductHydrator($uriFactory);
        }),
        'digitalsilk/dummyjson/api/command/products/list' => new Factory([
            'digitalsilk/dummyjson/api/client',
            'digitalsilk/dummyjson/api/hydrator/product',
        ], function (ApiClientInterface $client, TransformerInterface $hydrator): ListProductsCommandInterface {
                return new ApiListProductsCommand($client, $hydrator);
            }
        ),
    ];
};
