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
