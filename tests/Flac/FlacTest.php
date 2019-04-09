<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use PHPUnit\Framework\TestCase;

class FlacTest extends TestCase
{
    private $testDir;

    private $tmpDir;

    private $sampleFile;

    private $tmpFile;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->testDir . 'samples' . DIRECTORY_SEPARATOR . 'test.flac';

        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'test.flac';

        copy($this->sampleFile, $this->tmpFile);

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);
    }

    /**
     * Ensure that a FLAC file can be converted to an MP3 file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToMp3()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toMp3(
                $this->flacManipulator->getFile(),
                $this->tmpDir . 'foo.mp3'
            )
        );
    }

    /**
     * Ensure that a FLAC file can be converted to a WAV file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToWav()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toWav(
                $this->flacManipulator->getFile(),
                $this->tmpDir . 'foo.wav'
            )
        );
    }

    /**
     * Ensure that a FLAC file can be converted to an ALAC file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToAlac()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toAlac(
                $this->flacManipulator->getFile(),
                $this->tmpDir . 'foo.m4a'
            )
        );
    }
}
