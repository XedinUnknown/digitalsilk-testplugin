<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Transform;

use DigitalSilk\DummyJson\Data\Product;
use DigitalSilk\DummyJson\Data\ProductInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Transforms product data into a model.
 *
 * @psalm-immutable
 * @psalm-import-type ProductData from ProductInterface
 * @template-covariant Out of ProductInterface
 * @template-covariant In of ProductData
 * @implements TransformerInterface<Out, In>
 */
class ProductHydrator implements TransformerInterface
{
    protected UriFactoryInterface $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    /**
     * @inheritDoc
     *
     * @psalm-param In $value The value to transform.
     *
     * @psalm-return Out The transformation result.
     */
    public function transform($value)
    {
        $data = $value;
        /** @var Out $out */
        $out = new Product(
            $data['id'],
            $data['title'],
            $data['description'],
            $data['price'],
            $data['discountPercentage'],
            $data['rating'],
            $data['stock'],
            $data['brand'],
            $data['category'],
            $this->hydrateImage($data['thumbnail']),
            $this->hydrateImageList($data['images'])
        );

        return $out;
    }

    /**
     * Creates an image representation from a URL.
     *
     * @param string $url The URL of the image.
     * @return UriInterface An image representation.
     */
    protected function hydrateImage(string $url): UriInterface
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->uriFactory->createUri($url);
    }

    /**
     * Creates a representation of a list of images from their URLs.
     *
     * @param iterable<string> $urls The list of image URLs.
     * @return iterable<UriInterface> The image list representation.
     */
    protected function hydrateImageList(iterable $urls): iterable
    {
        foreach ($urls as $url) {
            yield $this->hydrateImage($url);
        }
    }
}
