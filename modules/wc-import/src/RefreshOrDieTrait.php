<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

/**
 * Able to redirect or display a final mesage.
 */
trait RefreshOrDieTrait
{
    /**
     * Redirects back to referrer if available, dies.
     *
     * Currently, this seems to never redirect, because of the `wp_die()` right after.
     * The intention was to display the death screen still, while the page may be redirecting,
     * or to use the death screen as a fallback in case the browser somehow fails to redirect.
     * This is fine for now, though, because otherwise there's no message at all to indicate
     * whether the user's action was successful.
     *
     * @todo Implement message on refreshed screen.
     *
     * @param string $message The message to display.
     * @param string $title The message title.
     *
     * @return never
     */
    protected function refreshOrDie(string $message, string $title = ''): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $dieOptions = ['back_link' => true];

        if ($referer) {
            wp_redirect($referer);
        }

        wp_die($message, $title, $dieOptions);
        die(); // Tells psalm that indeed this function terminates execution
    }
}
