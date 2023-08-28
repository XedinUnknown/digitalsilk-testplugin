<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Codec;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Something that can decode the data from a stream.
 */
interface StreamingDecoderInterface
{
    /**
     * Decodes data from a given stream.
     *
     * @param StreamInterface $stream The stream to decode.
     * @return iterable The decoded data.
     *
     * @throws RuntimeException If problem decoding.
     */
    public function decode(StreamInterface $stream): iterable;

    /**
     * Retrieves the MIME type of decodable data.
     *
     * @return string The MIME type.
     */
    public function getMimeType(): string;
}
