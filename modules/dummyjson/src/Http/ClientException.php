<?php

namespace DigitalSilk\DummyJson\Http;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * A problem with a client sending a request.
 */
class ClientException extends Exception implements ClientExceptionInterface
{
}
