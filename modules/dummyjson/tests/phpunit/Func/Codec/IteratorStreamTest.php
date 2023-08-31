<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func\Codec;

use Ancarda\Psr7\StringStream\StringStream;
use ArrayIterator;
use DigitalSilk\DummyJson\Codec\IteratorStream;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\Test\TestCase;
use Psr\Http\Message\StreamInterface;

class IteratorStreamTest extends TestCase
{
    public function testStream()
    {
        {
            $string = uniqid('data');
            $iterator = new ArrayIterator(str_split($string));
            $subject = new IteratorStream($iterator);
        }

        {
            $result = $subject->getContents();
            $this->assertEqualsCanonicalizing($string, $result);
        }
    }
}
