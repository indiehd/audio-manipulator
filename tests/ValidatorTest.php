<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR
            . 'samples' . DIRECTORY_SEPARATOR;

        $this->wavManipulatorCreator = app()->builder
            ->get('wav_manipulator_creator');

        $this->wavManipulator = $this->wavManipulatorCreator
            ->create($this->testDir . 'foo.wav');
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
