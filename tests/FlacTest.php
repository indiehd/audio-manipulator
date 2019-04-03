<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

class FlacTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR
            . 'samples' . DIRECTORY_SEPARATOR;

        $this->flacManipulatorCreator = app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->testDir . 'foo.flac');
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
                'bar.mp3'
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
                'bar.wav'
            )
        );
    }
}
