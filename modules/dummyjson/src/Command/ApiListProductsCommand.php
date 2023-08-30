<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Command;

use DigitalSilk\DummyJson\Data\CallbackSelectResult;
use DigitalSilk\DummyJson\Data\ProductInterface;
use DigitalSilk\DummyJson\Data\SelectResultInterface;
use DigitalSilk\DummyJson\Data\ProductsSelectResult;
use DigitalSilk\DummyJson\Http\ApiClientInterface;
use DigitalSilk\DummyJson\Http\DataResponseInterface;
use DigitalSilk\DummyJson\Transform\TransformerInterface;
use RuntimeException;
use Traversable;

/**
 * A command that uses an API to retrieve a list of products.
 *
 * @psalm-import-type ProductData from ProductInterface
 */
class ApiListProductsCommand implements ListProductsCommandInterface
{
    /** @var string */
    public const LIST_REQUEST_ENDPOINT = '/products';
    /** @var string */
    public const SEARCH_REQUEST_ENDPOINT = '/products/search';
    /** @var string */
    public const REQUEST_METHOD = ApiClientInterface::METHOD_GET;

    protected ApiClientInterface $client;
    protected TransformerInterface $transformer;

    /**
     * @param ApiClientInterface $client
     * @param TransformerInterface<ProductInterface, ProductData> $transformer
     */
    public function __construct(
        ApiClientInterface $client,
        TransformerInterface $transformer
    ) {
        $this->client = $client;
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function listProducts(?string $keyphrase = null, int $limit = 0, int $offset = 0): SelectResultInterface
    {
        $params = [
            'limit' => $limit,
            'skip' => $offset
        ];
        $endpoint = static::LIST_REQUEST_ENDPOINT;
        if ($keyphrase !== null) {
            $params['q'] = $keyphrase;
            $endpoint = static::SEARCH_REQUEST_ENDPOINT;
        }

        $response = $this->client->sendRequest($endpoint, static::REQUEST_METHOD, $params);
        $result = $this->createSelectResultFromResponse($response);

        return $result;
    }

    /**
     * @param DataResponseInterface $response
     * @return SelectResultInterface
     */
    protected function createSelectResultFromResponse(DataResponseInterface $response): SelectResultInterface
    {
        $data = $response->getData();
        if (is_null($data)) {
            throw new RuntimeException('Data has not been decoded');
        }

        /**
         * Transforms an iterable representing product data into a product model.
         * @param ProductData $productData
         * @return ProductInterface
         */
        $callback = function (iterable $productData): ProductInterface {
            // Normalize to array
            $data = $productData instanceof Traversable
                ? iterator_to_array($productData)
                : $productData;

            return $this->transformer->transform($data);
        };
        $result = new ProductsSelectResult($data); // Native structures
        $transformedResult = new CallbackSelectResult($result, $callback); // Model classes

        return $transformedResult;
    }
}
