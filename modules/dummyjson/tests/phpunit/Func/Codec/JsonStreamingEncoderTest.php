<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func\Codec;

use Ancarda\Psr7\StringStream\StringStream;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\Codec\JsonStreamingEncoder;
use DigitalSilk\DummyJson\Test\TestCase;
use Psr\Http\Message\StreamInterface;

class JsonStreamingEncoderTest extends TestCase
{
    public function testStream()
    {
        {
            $jsonString = $this->getDummyJson();
            $data = json_decode($jsonString, true);
            $subject = new JsonStreamingEncoder(true);
        }

        {
            $stream = $subject->encode($data);
            $streamContents = $stream->getContents();
            $result = json_decode($streamContents, true);

            $this->assertEqualsCanonicalizing($data, $result);
        }
    }

    /**
     * Retrieves the dummy JSON.
     *
     * @return string The data, encoded in a JSON string,
     */
    protected function getDummyJson()
    {
        $baseDir = $this->getModulePath();
        $dataFilePath = "$baseDir/tests/data/dummy.json";

        return file_get_contents($dataFilePath);
    }
}
