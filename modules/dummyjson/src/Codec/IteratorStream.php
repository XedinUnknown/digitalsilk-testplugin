<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Codec;

use Exception;
use Iterator;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * A stream that will emit data from an internal iterator when read.
 */
class IteratorStream implements StreamInterface
{
    protected int $index = 0;
    protected int $position = 0;
    protected string $data = '';
    protected ?Iterator $iterator = null;

    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Exception $e) {
            return 'ERROR';
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        $this->iterator = null;

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException If problem determining state.
     */
    public function eof(): bool
    {
        if ($this->iterator === null) {
            throw new RuntimeException('This stream is detached');
        }

        return $this->index !== 0
            && !$this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     *
     * @return never
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new RuntimeException('This stream is not seekable');
    }

    /**
     * @inheritDoc
     *
     * @return never
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function write(string $string): int
    {
        throw new RuntimeException('This stream is not writable');
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return $this->iterator !== null;
    }

    /**
     * @inheritDoc
     */
    public function read(int $length): string
    {
        $iterator = $this->iterator;
        if ($iterator === null) {
            throw new RuntimeException('This stream is detached');
        }

        // Ensure enough content
        do {
            // Advance
            if ($this->index === 0) {
                $iterator->rewind();
            } else {
                $iterator->next();
            }
            $this->index++;

            // Stop if end
            if (!$iterator->valid()) {
                break;
            }

            // Add content
            $data = (string) $iterator->current();
            $this->data .= $data;
            $this->position += strlen($data);
        } while (strlen($this->data) < $length);

        // Get chunk of content
        $output = substr($this->data, 0, $length);
        if ($output === false) {
            throw new RuntimeException(sprintf('Unable to get data chunk of length %1$d for output', $length));
        }

        // Consume buffer
        $data = substr($this->data, strlen($output));
        if ($data === false) {
            throw new RuntimeException(sprintf('Unable to get data chunk of length %1$d for output', $length));
        }
        $this->data = $data;

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        $contents = '';
        while (!$this->eof()) {
            $contents .= $this->read(1000);
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(?string $key = null)
    {
        return ($key === null) ? [] : null;
    }
}
