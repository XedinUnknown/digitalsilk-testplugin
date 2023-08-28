<?php

declare(strict_types=1);

namespace DigitalSilk\DummyJson\Test\Func\Codec;

use Ancarda\Psr7\StringStream\StringStream;
use DigitalSilk\DummyJson\Codec\JsonMachineDecoder;
use DigitalSilk\DummyJson\Test\TestCase;
use Psr\Http\Message\StreamInterface;

class JsonMachineDecoderTest extends TestCase
{
    public function testData()
    {
        {
            $jsonString = $this->getDummyJson();
            $expectedData = json_decode($jsonString, true);
            $rawData = new StringStream($jsonString);
            $decoder = new JsonMachineDecoder(['']);
            $data = $decoder->decode($rawData);
        }

        {
            $result = iterator_to_array($data);
            $this->assertEqualsCanonicalizing($expectedData, $result);
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

    /**
     * Creates a new stream with the given contents.
     *
     * @param string $contents The contents of the stream.
     *
     * @return StreamInterface The new stream.
     */
    protected function createStream(string $contents): StreamInterface
    {
        return new StringStream($contents);
    }
}
