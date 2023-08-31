<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Data;

use Psr\Http\Message\UriInterface;

/**
 * A product.
 */
class Product implements ProductInterface
{
    protected int $id;
    protected string $title;
    protected string $description;
    protected float $price;
    protected float $discountPercentage;
    protected float $rating;
    protected int $stock;
    protected string $brand;
    protected string $category;
    protected UriInterface $thumbnail;
    /** @var iterable<UriInterface> */
    protected iterable $images;

    /**
     * @param positive-int $id
     * @param string $title
     * @param string $description
     * @param float $price
     * @param float $discountPercentage
     * @param float $rating
     * @param int $stock
     * @param string $brand
     * @param string $category
     * @param UriInterface $thumbnail
     * @param iterable<UriInterface> $images
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        float $price,
        float $discountPercentage,
        float $rating,
        int $stock,
        string $brand,
        string $category,
        UriInterface $thumbnail,
        iterable $images
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->discountPercentage = $discountPercentage;
        $this->rating = $rating;
        $this->stock = $stock;
        $this->brand = $brand;
        $this->category = $category;
        $this->thumbnail = $thumbnail;
        $this->images = $images;
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @inheritDoc
     */
    public function getDiscountPercentage(): float
    {
        return $this->discountPercentage;
    }

    /**
     * @inheritDoc
     */
    public function getRating(): float
    {
        return $this->rating;
    }

    /**
     * @inheritDoc
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @inheritDoc
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnail(): UriInterface
    {
        return $this->thumbnail;
    }

    /**
     * @inheritDoc
     */
    public function getImages(): iterable
    {
        return $this->images;
    }
}
