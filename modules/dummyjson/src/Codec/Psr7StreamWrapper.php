<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Codec;

use Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;

use const STREAM_USE_PATH;
use const STREAM_REPORT_ERRORS;

/**
 * Converts PSR-7 streams into PHP stream resources.
 *
 * Adapted from Guzzle.
 *
 * @link https://github.com/guzzle/psr7/blob/2.6/src/StreamWrapper.php
 *
 * @psalm-type StreamMode = 'r'|'r+'|'w'
 */
class Psr7StreamWrapper
{
    /** @var lowercase-string */
    public const DEFAULT_PROTOCOL = 'psr7';
    public const CONTEXT_KEY_STREAM = 'stream';

    /** @var ?self */
    protected static ?self $instance = null;

    /**
     * The context of the current native stream. This should contain the standards-compliant stream instance.
     *
     * @var resource
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $context;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected StreamInterface $stream;

    /**
     * @var StreamMode
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected string $mode;

    /** @psalm-var lowercase-string */
    protected string $protocol;

    /**
     * Retrieves a resource representing the stream.
     *
     * @param StreamInterface $stream The stream to get a resource for
     *
     * @return resource The stream resource.
     *
     * @throws RangeException If stream is not readable or writable.
     * @throws RuntimeException If problem retrieving.
     */
    public static function getResource(StreamInterface $stream)
    {
        $wrapper = self::getInstance();
        $wrapper->register();

        if ($stream->isReadable()) {
            $mode = $stream->isWritable() ? 'r+' : 'r';
        } elseif ($stream->isWritable()) {
            $mode = 'w';
        } else {
            throw new RangeException('The stream must be readable, writable, or both');
        }

        return $wrapper->open($stream, $mode);
    }

    protected static function getInstance(): self
    {
        if (static::$instance === null) {
            $protocol = static::DEFAULT_PROTOCOL;
            static::$instance = new self($protocol);
        }

        return static::$instance;
    }

    /**
     * @psalm-param lowercase-string $protocol The protocol.
     */
    public function __construct(string $protocol = self::DEFAULT_PROTOCOL)
    {
        $this->protocol = $protocol;
    }

    /**
     * Opens a handle to the given stream, in the specified mode.
     *
     * @param StreamInterface $stream The stream to open a handle to.
     * @param StreamMode $mode The mode to open the handle in.
     *
     * @return resource The handle.
     *
     * @throws RuntimeException If problem opening.
     */
    public function open(StreamInterface $stream, string $mode)
    {
        $uri = $this->getStreamUri($stream);

        $handle = fopen($uri, $mode, false, static::createStreamContext($stream));
        if (!$handle) {
            throw new RuntimeException(sprintf('Could not open stream to "%1$s" in mode "%2$s"', $uri, $mode));
        }

        return $handle;
    }

    /**
     * Retrieves a URI that corresponds to the given stream instance.
     *
     * URI is not guaranteed to correspond to an actual resource.
     * However, it will be the same for the same stream _instance_.
     *
     * @param StreamInterface $stream The stream to get the URI for.
     *
     * @return string The URI that corresponds to the given stream.
     */
    protected function getStreamUri(StreamInterface $stream): string
    {
        $streamName = spl_object_hash($stream);
        $uri = $this->getPathUri($streamName);

        return $uri;
    }

    protected function getPathUri(string $path): string
    {
        $path = ltrim($path, '/');
        $uri = sprintf('%1$s://%2$s', $this->protocol, $path);

        return $uri;
    }

    /**
     * Creates a stream context containing the original standards-compliant stream.
     *
     * @param StreamInterface $stream The standards-compliant stream.
     *
     * @return resource
     */
    protected function createStreamContext(StreamInterface $stream)
    {
        return stream_context_create([
            static::DEFAULT_PROTOCOL => [
                static::CONTEXT_KEY_STREAM => $stream,
            ],
        ]);
    }

    /**
     * Retrieves a standards-compliant stream from a native stream context.
     *
     * @param resource $context The context of a native stream.
     *
     * @return StreamInterface The standards-compliant stream instance.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getStreamFromContext($context): StreamInterface
    {
        $options = stream_context_get_options($context);
        $wrapperOptionsKey = static::DEFAULT_PROTOCOL;
        $streamKey = static::CONTEXT_KEY_STREAM;

        if (!isset($options[$wrapperOptionsKey])) {
            throw new RangeException(
                sprintf(
                    'Context does not contain key "%1$s"',
                    $wrapperOptionsKey
                )
            );
        }
        $wrapperOptions = $options[$wrapperOptionsKey];

        if (!isset($wrapperOptions[$streamKey])) {
            throw new RangeException(
                sprintf(
                    'Context does not contain path "%1$s"',
                    join('.', [$wrapperOptionsKey, $streamKey])
                )
            );
        }
        $stream = $wrapperOptions[$streamKey];
        if (!$stream instanceof StreamInterface) {
            throw new RangeException(
                sprintf(
                    'Context at path "%1$s" does not contain a standards-compliant stream',
                    join('.', [$wrapperOptionsKey, $streamKey])
                )
            );
        }

        return $stream;
    }

    /**
     * Registers the stream wrapper, if not yet registered.
     */
    public function register(): void
    {
        $protocol = $this->protocol;
        if (!in_array($protocol, stream_get_wrappers())) {
            stream_wrapper_register($protocol, get_called_class());
        }
    }

    /**
     * Opens a stream at the specified path in the specified mode.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-open.php
     *
     * @param string $path The path to open the stream at.
     * @param StreamMode $mode The mode to open the stream in.
     * @param int-mask<STREAM_USE_PATH|STREAM_REPORT_ERRORS> $options Stream options.
     * @param string|null $opened_path The full path to the opened resource, if {@see STREAM_USE_PATH} is specified.
     *
     * @return bool True if the stream was opened successfully; false otherwise;
     *
     * @throws RuntimeException If problem opening.
     */
    public function stream_open(string $path, string $mode, int $options, string &$opened_path = null): bool
    {
        $this->mode = $mode;
        $this->stream = $this->getStreamFromContext($this->context);

        return true;
    }

    /**
     * Reads up to the specified number of bytes from the stream.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-read.php
     *
     * @param int $count The max number of bytes to read.
     *
     * @return string The contents of the stream from the current position, and up to the specified number of bytes.
     *
     * @throws RuntimeException If problem reading.
     */
    public function stream_read(int $count): string
    {
        return $this->stream->read($count);
    }

    /**
     * Writes the given data to the stream.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-write.php
     *
     * @param string $data The data to write.
     *
     * @return int The number of bytes written.
     *
     * @throws RuntimeException If problem writing.
     */
    public function stream_write(string $data): int
    {
        return $this->stream->write($data);
    }

    /**
     * Retrieves the current position in the stream.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-tell.php
     *
     * @return int The position.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function stream_tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * Determines whether the stream has reached End of File.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-eof.php
     *
     * @return bool True if End of File has been reached; false otherwise;
     *
     * @throws RuntimeException If problem determining state.
     */
    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * Seeks to a position in the stream;
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-seek.php
     *
     * @param int $offset The position.
     * @param int $whence Determines how the position will be calculated.
     *
     * @return bool True if seeked successfully; false otherwise;
     *
     * @throws RuntimeException If problem seeking.
     */
    public function stream_seek(int $offset, int $whence): bool
    {
        $this->stream->seek($offset, $whence);

        return true;
    }

    /**
     * Retrieve the underlying resource.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-cast.php
     *
     * @return resource|false The resource, if successful; false otherwise.
     */
    public function stream_cast(int $cast_as)
    {
        $stream = clone $this->stream;
        $resource = $stream->detach();

        return $resource ?? false;
    }

    /**
     * Retrieves information about a resource.
     *
     * @link https://www.php.net/manual/en/streamwrapper.stream-stat.php
     * @see fstat()
     *
     * @return array<int|string, int> The resource info.
     */
    public function stream_stat(): array
    {
        static $modeMap = [
            'r' => 33060,
            'rb' => 33060,
            'r+' => 33206,
            'w' => 33188,
            'wb' => 33188,
        ];

        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => $modeMap[$this->mode],
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => $this->stream->getSize() ?: 0,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
        ];
    }

    /**
     * Retrieves information about a URI.
     *
     * @link https://www.php.net/manual/en/streamwrapper.url-stat.php
     * @see stat()
     *
     * @return array<int|string, int>
     */
    public function url_stat(string $path, int $flags): array
    {
        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => 0,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
        ];
    }
}
