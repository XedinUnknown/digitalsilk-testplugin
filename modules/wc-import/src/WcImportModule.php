<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

use Dhii\Container\ServiceProvider;
use Dhii\Modular\Module\ModuleInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * A module that provides the DummyJSON SDK
 */
class WcImportModule implements ModuleInterface
{
    /**
     * @inheritDoc
     */
    public function setup(): ServiceProviderInterface
    {
        $srcDir = __DIR__;
        $rootDir = dirname($srcDir);

        return new ServiceProvider(
            (require "$srcDir/factories.php")($rootDir),
            (require "$srcDir/extensions.php")()
        );
    }

    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $c): void
    {
        add_action('init', function () use ($c) {
            $brandTaxonomyKey = 'brand';
            $productObjectType = 'product';
            /** @var string $brandTaxonomyName */
            $brandTaxonomyName = $c->get("digitalsilk/wc-import/taxonomy/$brandTaxonomyKey/name");
            /** @var array<string, mixed> $brandTaxonomyArgs */
            $brandTaxonomyArgs = $c->get("digitalsilk/wc-import/taxonomy/$brandTaxonomyKey/settings");

            register_taxonomy( $brandTaxonomyName, $productObjectType, $brandTaxonomyArgs );
            register_taxonomy_for_object_type( $brandTaxonomyName, $productObjectType );
        });
    }
}
