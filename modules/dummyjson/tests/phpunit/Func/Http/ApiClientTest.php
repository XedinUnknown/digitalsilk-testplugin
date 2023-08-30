<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func\Http;

use Ancarda\Psr7\StringStream\StringStream;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\DummyJsonModule;
use DigitalSilk\DummyJson\Http\ApiClientInterface;
use DigitalSilk\DummyJson\Http\AuthTokenProviderInterface;
use DigitalSilk\DummyJson\Test\ModulePathTrait;
use DigitalSilk\TestPlugin\Test\AbstractModularTestCase;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;

class ApiClientTest extends AbstractModularTestCase
{
    use ModulePathTrait;

    /**
     * Tests that the API client will correctly handle a successful response for a products request.
     */
    public function testRequestProducts()
    {
        {
            $authToken = uniqid('auth_token');
            $jsonString = $this->getDummyJson();
            $data = json_decode($jsonString, true);
            $httpClient = $this->createHttpClient($jsonString);
            $decoder = new JsonMachineDecoder(true, ['']);
            $container = $this->bootstrapModules([new DummyJsonModule()], [
                'digitalsilk/dummyjson/http/client' => fn() => $httpClient,
                'digitalsilk/dummyjson/api/decoder' => fn() => $decoder,
                'digitalsilk/dummyjson/api/auth/token_provider' => fn() => $this->createTokenProvider($authToken),
            ]);
            $subject = $container->get('digitalsilk/dummyjson/api/client');
            assert($subject instanceof ApiClientInterface);
        }

        {
            $response = $subject->sendRequest('/products', 'GET');
            $result = iterator_to_array($response->getData());

            $this->assertEqualsCanonicalizing($data['products'], $result['products']);
            $this->assertEquals($data['total'], $result['total']);
        }
    }

    /**
     * Retrieves the dummy JSON.
     *
     * @return string The data, encoded in a JSON string,
     */
    protected function getDummyJson()
    {
        $baseDir = $this->getModulePath();
        $dataFilePath = "$baseDir/tests/data/dummy.json";

        return file_get_contents($dataFilePath);
    }

    protected function createHttpClient(string $jsonString): ClientInterface
    {
        $mock = $this->getMockBuilder(ClientInterface::class)
            ->onlyMethods(['sendRequest'])
            ->getMockForAbstractClass();
        $body = new StringStream($jsonString);
        $response = new Response(200, ['Content-Type' => 'application/json'], $body);

        $mock->expects($this->exactly(1))
            ->method('sendRequest')
            ->will($this->returnValue($response));

        return $mock;
    }

    /**
     * Creates a new auth token provider with the specified token.
     *
     * @param string $authToken The auth token that will be provided.
     *
     * @return AuthTokenProviderInterface&MockObject
     */
    protected function createTokenProvider(string $authToken): AuthTokenProviderInterface
    {
        $mock = $this->getMockBuilder(AuthTokenProviderInterface::class)
            ->onlyMethods(['provideAuthToken'])
            ->getMockForAbstractClass();

        $mock->method('provideAuthToken')
            ->will($this->returnValue($authToken));

        return $mock;
    }
}
