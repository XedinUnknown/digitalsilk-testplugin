<?php

declare(strict_types=1);

use Dhii\Services\Factories\Alias;
use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\Codec\JsonStreamingEncoder;
use DigitalSilk\DummyJson\Codec\StreamingDecoderInterface;
use DigitalSilk\DummyJson\Codec\StreamingEncoderInterface;
use DigitalSilk\DummyJson\Http\ApiClient;
use DigitalSilk\DummyJson\Http\ApiClientInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use WpOop\HttpClient\Client;

return function (string $modDir): array {
    return [
        'digitalsilk/dummyjson/is_debug' => new Value(true),
        'digitalsilk/dummyjson/http/psr17_factory' => new Factory([], function (): Psr17Factory {
            $factory = new Psr17Factory();

            return $factory;
        }),
        'digitalsilk/dummyjson/http/request_factory' => new Alias('digitalsilk/dummyjson/http/psr17_factory'),
        'digitalsilk/dummyjson/http/uri_factory' => new Alias('digitalsilk/dummyjson/http/psr17_factory'),
        'digitalsilk/dummyjson/http/client' => new Factory([
            'digitalsilk/dummyjson/psr17_factory'
        ], function (ResponseFactoryInterface $responseFactory): ClientInterface {
            $httpClient = new Client([], $responseFactory);

            return $httpClient;
        }),
        'digitalsilk/dummyjson/codec/json_streaming_encoder' => new Constructor(JsonStreamingEncoder::class, [
            'digitalsilk/dummyjson/is_debug',
        ]),
        'digitalsilk/dummyjson/api/encoder' => new Alias('digitalsilk/dummyjson/codec/json_streaming_encoder'),
        'digitalsilk/dummyjson/api/decoder' => new Factory([
            'digitalsilk/dummyjson/is_debug',
        ], function (bool $isDebug): StreamingDecoderInterface {
            return new JsonMachineDecoder($isDebug, [
                '/products',
                '/total',
            ]);
        }),
        'digitalsilk/dummyjson/api/base_url' => new Value('https://dummyjson.com/'),
        'digitalsilk/dummyjson/api/client' => new Factory([
            'digitalsilk/dummyjson/api/base_url',
            'digitalsilk/dummyjson/http/client',
            'digitalsilk/dummyjson/http/uri_factory',
            'digitalsilk/dummyjson/http/request_factory',
            'digitalsilk/dummyjson/api/encoder',
            'digitalsilk/dummyjson/api/decoder',
        ], function (
            string $baseUrl,
            ClientInterface $httpClient,
            UriFactoryInterface $uriFactory,
            RequestFactoryInterface $requestFactory,
            StreamingEncoderInterface $encoder,
            StreamingDecoderInterface $decoder
        ): ApiClientInterface {
            $baseUrl = $uriFactory->createUri($baseUrl);

            $apiClient = new ApiClient(
                $baseUrl,
                $httpClient,
                $requestFactory,
                $uriFactory,
                $encoder,
                $decoder
            );

            return $apiClient;
        }),
    ];
};
