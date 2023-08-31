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
        /** @var callable $addNavigationHook */
        $addBrandTaxonomyHook = $c->get('digitalsilk/wc-import/hooks/add_brand_taxonomy');
        add_action('init', $addBrandTaxonomyHook);
    }
}
