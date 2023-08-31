<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

/**
 * Adds navigation elements to the admin UI.
 */
class AddNavigation
{
    /** @var callable */
    protected $renderSettingsPageHook;
    protected string $pageTitle;
    protected string $pageSlug;

    public function __construct(callable $renderSettingsPageHook, string $pageTitle, string $pageSlug)
    {
        $this->renderSettingsPageHook = $renderSettingsPageHook;
        $this->pageTitle = $pageTitle;
        $this->pageSlug = $pageSlug;
    }

    public function __invoke()
    {
        add_submenu_page(
            'woocommerce',
            $this->pageTitle,
            $this->pageTitle,
            'read',
            $this->pageSlug,
            $this->renderSettingsPageHook
        );
    }
}
