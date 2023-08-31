<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

use DigitalSilk\WcImport\RefreshOrDieTrait;
use DigitalSilk\WcImport\VerifyNonceTrait;
use RangeException;

/**
 * Saves incoming settings, and reloads page.
 */
class SaveSettings
{
    use VerifyNonceTrait;
    use RefreshOrDieTrait;

    public function __invoke()
    {
        try {
            $this->verifyNonce('digitalsilk-testplugin-settings-save');
        } catch (RangeException $e) {
            wp_die($e->getMessage(), 'Failure!', ['back_link' => true]);
        }

        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $batchSize = $_POST['batch_size'] ?? null;

        if (is_string($username)) {
            update_option('digitalsilk/wc-import/dummyjson/username', $username);
        }
        if (is_string($password)) {
            update_option('digitalsilk/wc-import/dummyjson/password', $password);
        }
        if (is_numeric($batchSize)) {
            update_option('digitalsilk/wc-import/batch_size', intval($batchSize));
        }

        $this->refreshOrDie('Options updated!', 'Success!');
    }
}
