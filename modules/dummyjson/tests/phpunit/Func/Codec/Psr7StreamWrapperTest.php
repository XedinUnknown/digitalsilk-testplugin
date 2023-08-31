<?php

namespace DigitalSilk\DummyJson\Test\Func\Codec;

use Ancarda\Psr7\StringStream\StringStream;
use DigitalSilk\DummyJson\Codec\Psr7StreamWrapper;
use PHPUnit\Framework\TestCase;

class Psr7StreamWrapperTest extends TestCase
{
    public function testStreamRead()
    {
        {
            $data = uniqid('stream-data');
            $stdStream = new StringStream($data);
            $stdStream->rewind();
        }

        {
            $nativeStream = Psr7StreamWrapper::getResource($stdStream);
            $result = stream_get_contents($nativeStream);

            $this->assertEquals($data, $result);
        }
    }

    public function testStreamWriteRead()
    {
        {
            $data = uniqid('stream-data');
            $stdStream = new StringStream('');
            $stdStream->rewind();
        }

        {
            // Write
            $nativeStream = Psr7StreamWrapper::getResource($stdStream);
            fwrite($nativeStream, $data);

            // Read
            $stdStream->rewind();
            $result = $stdStream->getContents();

            $this->assertEquals($data, $result);
        }
    }
}
