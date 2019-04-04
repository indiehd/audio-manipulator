<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use PHPUnit\Framework\TestCase;

class FlacTaggingTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->testDir . 'foo.flac');
    }

    /**
     * Ensure that a FLAC file can be tagged with metadata.
     *
     * @return void
     */
    public function testFlacTaggerCanTagFlacFile()
    {
        $this->assertIsArray(
            $this->flacManipulator->tagger->writeTags(
                $this->flacManipulator->getFile(),
                $this->flacManipulator->tagger->generateGetid3Tag(1)
            )
        );
    }
}
