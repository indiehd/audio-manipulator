<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use PHPUnit\Framework\TestCase;

class FlacTaggingTest extends TestCase
{
    private $testDir;

    private $tmpDir;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->testDir . 'samples' . DIRECTORY_SEPARATOR . 'test.flac');
    }

    /**
     * Ensure that a FLAC file can be tagged with metadata.
     *
     * @return void
     */
    public function testFlacTaggerCanTagFlacFile()
    {
        $tagData = [
            'songId' => 1,
            'name' => 'Test Song',
            'songOrder' => 1,
            'trackPreviewStart' => 0,
            'moniker' => 'Foobius Barius',
            'title' => 'Test Title',
            'year' => '1981',
            'genre' => 1,
            'albumId' => 1,
            'license' => 'All rights reserved.',
        ];

        $this->flacManipulator->tagger->writeTags(
            $this->flacManipulator->getFile(),
            $tagData
        );

        $fileDetails = $this->flacManipulator
            ->tagger
            ->getid3
            ->analyze($this->flacManipulator->getFile());

        $this->assertEquals(
            [
                'title' => [$tagData['name']],
                'artist' => [$tagData['moniker']],
                'date' => [$tagData['year']],
                'description' => [$tagData['license']],
                'album' => [$tagData['title']],
                'discnumber' => ['1/1'],
                'tracknumber' => [$tagData['songOrder'] . '/1'],
                'genre' => ['Rock'],
            ],
            $fileDetails['tags']['vorbiscomment']
        );
    }
}
