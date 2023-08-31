<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

use RangeException;
use RuntimeException;

/**
 * Able to verify a nonce from request data.
 */
trait VerifyNonceTrait
{
    /**
     * Determines whether the request data contains a valid nonce.
     *
     * @param ?string $action Name of the action, for which to verify the nonce.
     * @param string $fieldName Name of the POST field that must contain the nonce.
     *
     * @throws RangeException If nonce invalid.
     * @throws RuntimeException If problem verifying.
     */
    protected function verifyNonce(?string $action = null, string $fieldName = '_wpnonce'): void
    {
        $action = $action ?? -1;
        $nonce = $_POST[$fieldName] ?? null;

        if (!is_string($nonce) || empty($nonce)) {
            throw new RangeException('Nonce is absent from request data');
        }

        if (wp_verify_nonce($nonce, $action) !== 1) {
            throw new RangeException(sprintf('Nonce "%1$s" is invalid (possibly expired)', $nonce));
        }
    }
}
