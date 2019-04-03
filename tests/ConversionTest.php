<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;

class ConversionTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

        $this->flacManipulatorCreator = app()->builder->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator->create($this->testDir . 'foo.flac');
    }

    /**
     * Ensure that a FLAC file can be transcoded to an MP3 file.
     *
     * @return void
     */
    public function testTranscodingFlacToMp3Succeeds()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toMp3(
                $this->flacManipulator->getFile(),
                $this->testDir . 'foo.mp3'
            )
        );
    }

    /**
     * Ensure that a WAV file can be converted to a FLAC file.
     *
     * @return void
     */
    public function testConvertingWavToFlacSucceeds()
    {
        $this->assertIsArray($this->transcoder->convertWavToFlac(
            $this->testDir . 'foo.wav',
            $this->testDir . 'foo.flac'
        )['result']);
    }

    /**
     * Ensure that a WAV file can be transcoded to an MP3 file.
     *
     * @return void
     */
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

    /**
     * Ensure that a FLAC file can be converted to an ALAC file.
     *
     * @return void
     */
    public function testConvertingFlacToAlacSucceeds()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toAlac(
                $this->flacManipulator->getFile(),
                $this->testDir . 'foo.m4a'
            )
        );
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

    public function testWhenClipLengthIsSpecifiedAudioIsTrimmed()
    {
        $clipLength = 1;

        $oldFileDetails = $this->transcoder
            ->validator
            ->mediaParser
            ->analyze($this->testDir . 'foo.flac');

        // This test requires that the specified clip-length is less than the
        // input track length.

        $this->assertLessThan($oldFileDetails['playtime_seconds'], $clipLength);

        $newFileDetails = $this->transcoder->transcode(
            $this->testDir . 'foo.flac',
            $this->testDir . 'baz.flac',
            0,
            $clipLength
        );

        // The playtime in seconds should equal the specified clip-length.

        $this->assertTrue($newFileDetails['playtime_seconds'] === 1);
    }
}
