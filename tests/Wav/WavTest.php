<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

class WavTest extends TestCase
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

        $this->sampleFile = $this->testDir . 'samples' . DIRECTORY_SEPARATOR . 'test.wav';

        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'test.wav';

        copy($this->sampleFile, $this->tmpFile);

        $this->wavManipulatorCreator = app()->builder
            ->get('wav_manipulator_creator');

        $this->wavManipulator = $this->wavManipulatorCreator
            ->create($this->tmpFile);
    }

    /**
     * Ensure that a WAV file can be converted to an MP3 file.
     *
     * @return void
     */
    public function testWavManipulatorCanConvertToMp3()
    {
        $this->assertIsArray(
            $this->wavManipulator->converter->toMp3(
                $this->wavManipulator->getFile(),
                $this->tmpDir . 'foo.mp3'
            )
        );
    }

    /**
     * Ensure that a WAV file can be converted to a FLAC file.
     *
     * @return void
     */
    public function testWavManipulatorCanConvertToFlac()
    {
        $this->assertIsArray(
            $this->wavManipulator->converter->toFlac(
                $this->wavManipulator->getFile(),
                $this->tmpDir . 'foo.flac'
            )
        );
    }

    /**
     * Ensure that a WAV file can be converted to an ALAC file.
     *
     * @return void
     */
    public function testWavManipulatorCanConvertToAlac()
    {
        $this->assertIsArray(
            $this->wavManipulator->converter->toAlac(
                $this->wavManipulator->getFile(),
                $this->tmpDir . 'foo.m4a'
            )
        );
    }
}
