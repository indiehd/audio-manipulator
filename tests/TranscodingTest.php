<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;

class TranscodingTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->transcoder = app()->builder->get('transcoder');

        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
    }

    public function testTranscodingFlacToMp3Succeeds()
    {
        $this->testDir =

        $this->assertIsArray($this->transcoder->transcode(
            $this->testDir . 'foo.flac',
            $this->testDir . 'foo.mp3'
        ));
    }

    public function testConvertingWavToFlacSucceeds()
    {
        $this->assertIsArray($this->transcoder->convertWavToFlac(
            $this->testDir . 'foo.wav',
            $this->testDir . 'foo.flac'
        )['result']);
    }

    public function testTranscodingWavToMp3Succeeds()
    {
        $this->assertIsArray($this->transcoder->transcode(
            $this->testDir . 'foo.flac',
            $this->testDir . 'foo.wav'
        ));

        $this->assertTrue($this->transcoder->wavToMp3(
            $this->testDir . 'foo.wav',
            $this->testDir . 'foo.mp3',
            'cbr',
            128
        )['result']);
    }

    public function testConvertingFlacToAlacSucceeds()
    {
        $this->assertTrue($this->transcoder->transcodeFlacToAlac(
            $this->testDir . 'foo.flac'
        )['result']);
    }

    public function testExceptionIsThrownWhenInputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->transcoder->transcode(
            $this->testDir . 'bar.flac',
            $this->testDir . 'bar.wav'
        );
    }

    public function testExceptionIsThrownWhenInputFileTypeIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->transcoder->transcode(
            $this->testDir . 'foo.txt',
            $this->testDir . 'foo.wav'
        );
    }
}
