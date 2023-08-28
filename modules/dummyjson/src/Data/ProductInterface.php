<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use Psr\Http\Message\UriInterface;

/**
 * Represents a Product.
 *
 * @link https://dummyjson.com/docs/products
 *
 * @psalm-type ProductData = array{
 *        id: positive-int,
 *        title: string,
 *        description: string,
 *        price: float,
 *        discountPercentage: float,
 *        rating: float,
 *        stock: int,
 *        brand: string,
 *        category: string,
 *        thumbnail: string,
 *        images: array<string>
 *    }
 */
interface ProductInterface
{
    /**
     * Retrieves the ID associated with this instance.
     *
     * @return int The ID.
     */
    public function getId(): int;

    /**
     * Retrieves the title associated with this instance.
     *
     * @return string The title.
     */
    public function getTitle(): string;

    /**
     * Retrieves the description associated with this instance.
     *
     * @return string The description.
     */
    public function getDescription(): string;

    /**
     * Retrieves the price associated with this instance.
     *
     * @return float The price.
     */
    public function getPrice(): float;

    /**
     * Retrieves the discount percentage associated with this instance.
     *
     * @return float The discount percentage.
     */
    public function getDiscountPercentage(): float;

    /**
     * Retrieves the rating associated with this instance.
     *
     * @return float The rating.
     */
    public function getRating(): float;

    /**
     * Retrieves the stock associated with this instance.
     *
     * @return int The amount in stock.
     */
    public function getStock(): int;

    /**
     * Retrieves the brand associated with this instance.
     *
     * @return string The brand name.
     */
    public function getBrand(): string;

    /**
     * Retrieves the category associated with this instance.
     *
     * @return string The category slug.
     */
    public function getCategory(): string;

    /**
     * Retrieves the thumbnail associated with this instance.
     *
     * @return UriInterface The image URL.
     */
    public function getThumbnail(): UriInterface;

    /**
     * Retrieves the images associated with this instance.
     *
     * @return iterable<UriInterface> A list of image URLs.
     */
    public function getImages(): iterable;
}
