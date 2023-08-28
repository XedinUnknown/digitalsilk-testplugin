<?php

namespace DigitalSilk\DummyJson\Http;

use RangeException;

/**
 * An error reported by an API.
 */
class ApiException extends RangeException implements ApiExceptionInterface
{
}
