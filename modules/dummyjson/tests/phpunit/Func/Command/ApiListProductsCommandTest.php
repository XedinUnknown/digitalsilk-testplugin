<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func\Command;

use Ancarda\Psr7\StringStream\StringStream;
use DigitalSilk\DummyJson\Command\ListProductsCommandInterface;
use DigitalSilk\DummyJson\Data\ProductInterface;
use DigitalSilk\DummyJson\DummyJsonModule;
use DigitalSilk\DummyJson\Test\ModulePathTrait;
use DigitalSilk\TestPlugin\Test\AbstractModularTestCase;
use Generator;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ApiListProductsCommandTest extends AbstractModularTestCase
{
    use ModulePathTrait;

    /**
     * Tests that the command will successfully retrieve and prepare the results.
     */
    public function testListProducts()
    {
        {
            $jsonString = $this->getDummyJson();
            $data = json_decode($jsonString, true);
            $comparedProduct = $data['products'][0];
            $httpClient = $this->createHttpClient($jsonString);
            $container = $this->bootstrapModules([new DummyJsonModule()], [
                'digitalsilk/dummyjson/http/client' => fn() => $httpClient,
            ]);
            $subject = $container->get('digitalsilk/dummyjson/api/command/products/list');
            assert($subject instanceof ListProductsCommandInterface);
        }

        {
            $products = $subject->listProducts();
            foreach ($products as $product) {
                /** @var ProductInterface $product */
                $images = call_user_func_array(function (iterable $list): Generator {
                    foreach ($list as $image) {
                        /** @var UriInterface $image */
                        yield (string) $image;
                    }
                }, [$product->getImages()]);
                $result = [
                    'id' => $product->getId(),
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'discountPercentage' => $product->getDiscountPercentage(),
                    'rating' => $product->getRating(),
                    'stock' => $product->getStock(),
                    'brand' => $product->getBrand(),
                    'category' => $product->getCategory(),
                    'thumbnail' => (string) $product->getThumbnail(),
                    'images' => iterator_to_array($images),
                ];
                $this->assertEqualsCanonicalizing($comparedProduct, $result);

                // Only test the first item
                break;
            }
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
        $body = $this->getStreamForString($jsonString);
        $body->rewind();
        $response = new Response(200, ['Content-Type' => 'application/json'], $body);

        $mock->expects($this->exactly(1))
            ->method('sendRequest')
            ->will($this->returnValue($response));

        return $mock;
    }

    protected function getStreamForString(string $contents): StreamInterface
    {
        return new StringStream($contents);
    }
}
