<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

use DigitalSilk\DummyJson\Data\ProductInterface;
use Psr\Http\Message\UriInterface;
use RangeException;
use RuntimeException;
use Traversable;
use UnexpectedValueException;
use WC_Product;
use WC_Product_Simple;
use WP_Error;
use WP_Term;

/**
 * Can import a product.
 */
class ProductImporter implements ProductImporterInterface
{
    /**
     * phpcs:ignore SlevomatCodingStandard.TypeHints.UselessConstantTypeHint.UselessVarAnnotation
     * @var string
     */
    protected const TAXONOMY_NAME_PRODUCT_CAT = 'product_cat';
    /**
     * phpcs:ignore SlevomatCodingStandard.TypeHints.UselessConstantTypeHint.UselessVarAnnotation
     * @var string
     */
    protected const TAXONOMY_NAME_BRAND = 'brand';

    protected string $taxonomyNameCategories;
    protected string $taxonomyNameBrands;

    public function __construct(string $taxonomyNameCategories, string $taxonomyNameBrands)
    {
        $this->taxonomyNameCategories = $taxonomyNameCategories;
        $this->taxonomyNameBrands = $taxonomyNameBrands;
    }

    /**
     * @inheritDoc
     */
    public function importProduct(ProductInterface $product): WC_Product
    {
        $wcProduct = new WC_Product_Simple();

        // Main image
        $imageId = $this->downloadImage((string) $product->getThumbnail());
        $wcProduct->set_image_id($imageId);

        // Other images
        $otherImageIds = iterator_to_array($this->mapIterable(
            $product->getImages(),
            fn(UriInterface $url): int => $this->downloadImage((string) $url)
        ));
        $wcProduct->set_gallery_image_ids($otherImageIds);

        // Category
        $categorySlug = $product->getCategory();
        if (strlen($categorySlug)) {
            $category = $this->getTermForSlug($categorySlug, $this->taxonomyNameCategories);
            $wcProduct->set_category_ids([$category->term_id]);
        }

        // Brand
        $brandSlug = $product->getBrand();
        if (strlen($brandSlug)) {
            $brand = $this->getTermForSlug($brandSlug, $this->taxonomyNameBrands);
            // Assigned after product is saved, because it needs object ID
        }

        // Price
        $price = $product->getPrice();
        $wcProduct->set_regular_price((string) $price);

        // Discount
        $discountPercentage = $product->getDiscountPercentage();
        if ($discountPercentage > 0) {
            $discountedPrice = $price - ($price * $discountPercentage / 100.00);
            $wcProduct->set_sale_price((string) $discountedPrice);
        }

        // Rating
        $rating = $product->getRating();
        // Appears to have no effect: it should be an average from existing ratings, not explicitly set
        $wcProduct->set_average_rating($rating);

        // Other
        $wcProduct->set_name($product->getTitle());
        $wcProduct->set_short_description($product->getDescription());

        // Stock
        $stockQty = $product->getStock();
        if ($stockQty > 0) {
            $wcProduct->set_stock_quantity($stockQty);
            $wcProduct->set_manage_stock(true);
        }


        // Extra
        $wcProduct->update_meta_data('discount_percentage', (string) $discountPercentage);
        $wcProduct->update_meta_data('rating', (string) $rating);
        // The spec says to exclude this. Without the original ID,
        // there's no way to sync existing products with API.
//        $wcProduct->update_meta_data('original_id', $product->getId());

        $wcProduct->save();

        // Operations that require product ID
        /** @psalm-suppress PossiblyUndefinedVariable */
        if ($brand) {
            /** @var int $brandId */
            $brandId = $brand->term_id;
            wp_set_object_terms($wcProduct->get_id(), $brandId, $this->taxonomyNameBrands);
        }

        return $wcProduct;
    }

    /**
     * Downloads the image at the specified URL and adds it to the media library.
     *
     * @link https://rudrastyh.com/wordpress/how-to-add-images-to-media-library-from-uploaded-files-programmatically.html#upload-image-from-url
     *
     * @param string $url The URL to download the image from.
     *
     * @return int The attachment ID.
     *
     * @throws RuntimeException If problem downloading.
     */
    protected function downloadImage(string $url): int
    {
        $tempFilePath = download_url($url);
        if ($tempFilePath instanceof WP_Error) {
            throw new RangeException(
                sprintf('Could not download file from "%1$s": %2$s', $url, $tempFilePath->get_error_message())
            );
        }

        $file = array(
            'name'     => basename( $url ),
            'tmp_name' => $tempFilePath,
        );

        $contentType = mime_content_type( $tempFilePath );
        if (is_string($contentType)) {
            $file['type'] = $contentType;
        }

        $fileSize = filesize($tempFilePath);
        if (is_int($fileSize)) {
            $file['size'] = $fileSize;
        }

        $sideload = wp_handle_sideload($file, ['test_form' => false]);
        if(!empty($sideload[ 'error' ])) {
            throw new UnexpectedValueException((string) $sideload['error']);
        }

        $filePath = (string)$sideload['file'];

        // Add image to media library
        $attachmentId = wp_insert_attachment(
            array(
                'guid'           => $sideload['url'],
                'post_mime_type' => $sideload['type'],
                'post_title'     => basename($filePath),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $filePath
        );

        if( $attachmentId instanceof WP_Error || $attachmentId === 0 ) {
            $message = $attachmentId instanceof WP_Error
                ? $attachmentId->get_error_message()
                : sprintf(
                    'Could not inset attachment "%1$s" from "%2$s" into media library',
                    $filePath,
                    $url
                );

            throw new UnexpectedValueException($message);
        }

        // Update metadata, regenerate image sizes.
        // Gives false-negatives if new data is same as old,
        // so error handling is complicated, and in this case unnecessary.
        wp_update_attachment_metadata(
            $attachmentId,
            wp_generate_attachment_metadata($attachmentId, $filePath)
        );

        return $attachmentId;
    }

    /**
     * Converts the given iterable into another that will apply the given callback to each item.
     *
     * @template TKey
     * @template TValue
     * @template TReturn
     * @psalm-mutation-free
     *
     * @param iterable<TKey, TValue> $iterable Callback will be applied to each item.
     * @param callable(TValue, TKey): TReturn $callback The callback that will be applied.
     *
     * @return Traversable<TKey, TReturn> The iterable that will apply the callback when iterated over.
     */
    protected function mapIterable(iterable $iterable, callable $callback): Traversable
    {
        foreach ($iterable as $key => $element) {
            /** @psalm-suppress ImpureFunctionCall */
            yield $key => $callback($element, $key);
        }
    }

    protected function getTermForSlug(string $slug, string $taxonomyName): WP_Term
    {
        /** @var WP_Term|false $category */
        $category = get_term_by('slug', $slug, $taxonomyName);
        // Create category if not exists
        if (!$category) {
            $categoryTitle = $this->getTitleForSlug($slug);

            $termData = wp_insert_term($categoryTitle, $taxonomyName, [
                'slug' => $slug,
            ]);
            if ($termData instanceof WP_Error) {
                throw new RuntimeException(
                    sprintf(
                        'Could not create new term for taxonomy "%1$s" with slug "%2$s": %3$s',
                        $taxonomyName,
                        $slug,
                        $termData->get_error_message()
                    )
                );
            }

            $termId = $termData['term_id'];
            /** @var WP_Term|WP_Error $category */
            $category = get_term($termId);
            if ($category instanceof WP_Error) {
                throw new RuntimeException(
                    sprintf('Could not retrieve new term #%1$d: %2$s', $termId, $category->get_error_message())
                );
            }
        }

        return $category;
    }

    /**
     * Retrieves a human-readable title from the specified slug.
     *
     * @param string $slug The slug to generate a title from.
     *
     * @return string The title that corresponds to the slug.
     */
    protected function getTitleForSlug(string $slug): string
    {
        return ucwords(str_replace('-', ' ', $slug));
    }
}
