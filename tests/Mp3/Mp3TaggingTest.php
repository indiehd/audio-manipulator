<?php

namespace IndieHD\AudioManipulator\Tests\Mp3;

use function IndieHD\AudioManipulator\app;

use IndieHD\AudioManipulator\Tests\Tagging\TaggingTest;

class Mp3TaggingTest extends TaggingTest
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
        $this->setFileType('mp3');

        // Convert the master FLAC audio sample to MP3.

        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->sampleDir = $this->testDir . 'samples' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->sampleDir . 'test.flac';

        $this->tmpFile = $this->tmpDir . 'test.flac';

        copy($this->sampleFile, $this->tmpFile);

        $this->flacManipulatorCreator = app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);



        $this->{$this->fileType . 'ManipulatorCreator'} = app()->builder
            ->get($this->fileType . '_manipulator_creator');

        $this->{$this->fileType . 'Manipulator'} = $this->{$this->fileType . 'ManipulatorCreator'}
            ->create($this->tmpFile);



        $this->flacManipulator->tagger->removeAllTags(
            $this->flacManipulator->getFile()
        );

        $mp3Sample = $this->tmpDir . uniqid() . '.mp3';

        $this->flacManipulator->converter->toMp3(
            $this->flacManipulator->getFile(),
            $mp3Sample
        );

        // Use the newly-created sample MP3 file for testing.



        $this->mp3Manipulator = $this->mp3ManipulatorCreator
            ->create($mp3Sample);
    }

    protected function setFileType(string $type): void
    {
        $this->fileType = $type;
    }

    public function testItCanEmbedArtwork()
    {
        $this->embedArtwork();

        $fileDetails = $this->{$this->fileType . 'Manipulator'}->tagger->getid3->analyze(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );

        $testImage = file_get_contents($this->sampleDir . 'flac-logo.gif');

        $this->assertEquals(
            $testImage,
            $fileDetails['comments']['picture'][0]['data']
        );

        $this->assertEquals(
            $testImage,
            $fileDetails['id3v2']['APIC'][0]['data']
        );
    }

    /**
     * Ensure that an MP3 file can be tagged with metadata.
     *
     * @return void
     */
    public function testItCanTagFile()
    {
        $tagData = [
            'song' => ['Test Song'],
            'artist' => ['Foobius Barius'],
            'year' => ['1981'],
            'comment' => ['All rights reserved.'],
            'album' => ['Test Title'],
            'track' => ['1/1'],
            'genre' => ['Rock'],
        ];

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );

        $fileDetails = $this->{$this->fileType . 'Manipulator'}
            ->tagger
            ->getid3
            ->analyze($this->{$this->fileType . 'Manipulator'}->getFile());

        $this->assertEquals(
            [
                'title' => $tagData['song'],
                'artist' => $tagData['artist'],
                'year' => $tagData['year'],
                'recording_time' => $tagData['year'],
                'comment' => $tagData['comment'],
                'album' => $tagData['album'],
                'track_number' => [$tagData['track'][0]],
                'genre' => ['Rock'],
            ],
            $fileDetails['tags']['id3v2']
        );
    }

    protected function removeAllTags()
    {
        $this->{$this->fileType . 'Manipulator'}->tagger->removeAllTags(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );
    }

    public function testItCanRemoveArtworkFromFile()
    {
        $this->removeAllTags();

        $this->embedArtwork();

        $this->removeArtwork();

        $fileDetails = $this->{$this->fileType . 'Manipulator'}->tagger->getid3->analyze(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );

        $this->assertArrayNotHasKey('comments', $fileDetails);

        $this->assertArrayNotHasKey('APIC', $fileDetails['id3v2']);
    }

    public function testItCanRemoveTagsFromFile()
    {
        $this->removeAllTags();

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            [
                'artist' => ['Foo'],
            ]
        );

        $this->{$this->fileType . 'Manipulator'}->removeTags(
            [
                'TPE1',
            ]
        );

        $fileDetails = $this->{$this->fileType . 'Manipulator'}->tagger->getid3->analyze(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );

        $this->assertArrayNotHasKey('tags', $fileDetails);
    }

    public function testItCanWriteUtf8TagValuesAccurately()
    {
        $tagData = [
            'artist' => ['ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ'],
        ];

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );

        $fileDetails = $this->{$this->fileType . 'Manipulator'}
            ->tagger
            ->getid3
            ->analyze($this->{$this->fileType . 'Manipulator'}->getFile());

        $this->assertEquals(
            [
                'artist' => $tagData['artist'],
            ],
            $fileDetails['tags']['id3v2']
        );

        $this->assertEquals(
            [
                'artist' => $tagData['artist'],
            ],
            $fileDetails['id3v2']['comments']
        );

        $this->assertEquals(
            $tagData['artist'][0],
            $fileDetails['id3v2']['TPE1'][0]['data']
        );
    }
}
