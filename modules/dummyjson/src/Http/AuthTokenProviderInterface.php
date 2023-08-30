<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Http;

/**
 * Represents something that provides a valid authentication token.
 */
interface AuthTokenProviderInterface
{
    /**
     * Provides a valid authentication token.
     *
     * @return string The token.
     *
     * @throws ApiException If a valid token could not be provided by the API.
     * @throw RuntimeException If problem providing.
     */
    public function provideAuthToken(): string;
}
