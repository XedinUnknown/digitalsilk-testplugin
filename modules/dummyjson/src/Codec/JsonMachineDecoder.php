<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Codec;

use JsonMachine\Exception\JsonMachineException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Can decode data from a stream.
 */
class JsonMachineDecoder implements StreamingDecoderInterface
{
    protected const MIME_TYPE = 'application/json';
    protected bool $isDebug;
    /** @var array<string> */
    protected array $jsonPointers;

    public function __construct(bool $isDebug = false, array $jsonPointers = [''])
    {
        $this->isDebug = $isDebug;
        $this->jsonPointers = $jsonPointers;
    }

    /**
     * @inheritDoc
     */
    public function decode(StreamInterface $stream): iterable
    {
        $resource = Psr7StreamWrapper::getResource($stream);

        try {
            $data = Items::fromStream($resource, [
                'decoder' => new ExtJsonDecoder(true),
                'pointer' => $this->jsonPointers,
                'debug' => $this->isDebug,
            ]);
        } catch (JsonMachineException $e) {
            throw new RuntimeException('Could not create iterable from stream', 0, $e);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): string
    {
        return static::MIME_TYPE;
    }
}
