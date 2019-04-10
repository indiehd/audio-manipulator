<?php

namespace IndieHD\AudioManipulator\Tests\Mp3;

use PHPUnit\Framework\TestCase;

class Mp3TaggingTest extends TestCase
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
        // Convert the master FLAC audio sample to MP3.

        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->testDir . 'samples' . DIRECTORY_SEPARATOR . 'test.flac';

        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'test.flac';

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->sampleFile);

        $mp3Sample = $this->tmpDir . uniqid() . '.mp3';

        $this->flacManipulator->converter->toMp3(
            $this->flacManipulator->getFile(),
            $mp3Sample
        );

        // Use the newly-created sample MP3 file for testing.

        $this->mp3ManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('mp3_manipulator_creator');

        $this->mp3Manipulator = $this->mp3ManipulatorCreator
            ->create($mp3Sample);
    }

    /**
     * Ensure that an MP3 file can be tagged with metadata.
     *
     * @return void
     */
    public function testMp3TaggerCanTagMp3File()
    {
        $tagData = [
            'title' => ['Test Title'],
            'artist' => ['Foobius Barius'],
            'album' => ['Foobar\'s Fiddle-Along'],
            'track_number' => [1],
            'comment' => ['Copyright (c) 2018, Foobius Barius. All Rights Reserved.'],
            'genre' => ['Rock'],
            'year' => [2018],
        ];

        $this->mp3Manipulator->tagger->writeTags(
            $this->mp3Manipulator->getFile(),
            $tagData
        );

        $fileDetails = $this->mp3Manipulator
            ->tagger
            ->getid3
            ->analyze($this->mp3Manipulator->getFile());

        $this->assertEquals(
            [
                'title' => $tagData['title'],
                'artist' => $tagData['artist'],
                'comment' => $tagData['comment'],
                'album' => $tagData['album'],
                'track_number' => [$tagData['track_number'][0]],
                'genre' => ['Rock'],
                'recording_time' => $tagData['year'],
                'year' => $tagData['year'],
            ],
            $fileDetails['tags']['id3v2']
        );
    }
}
