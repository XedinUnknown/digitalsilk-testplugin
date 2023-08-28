<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Codec;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Something that can encode data in a stream.
 *
 * @psalm-immutable
 *
 * @template-covariant Subject of iterable
 */
interface StreamingEncoderInterface
{
    /**
     * Encodes the given data in a steam.
     *
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
     * @param Subject $data The data to encode.
     *
     * @return StreamInterface The stream that encoded data will be written to.
     *
     * @throws RuntimeException If problem encoding.
     */
    public function encode(iterable $data): StreamInterface;

    /**
     * Retrieves the MIME type of encoded data.
     *
     * @return string The MIME type.
     */
    public function getMimeType(): string;
}
