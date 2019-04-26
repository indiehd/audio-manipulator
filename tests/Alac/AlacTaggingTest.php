<?php

namespace IndieHD\AudioManipulator\Tests\Alac;

use function IndieHD\AudioManipulator\app;

use IndieHD\AudioManipulator\Tests\Tagging\TaggingTest;

class AlacTaggingTest extends TaggingTest
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
        $this->setFileType('alac');

        // Define filesystem paths for use in testing.

        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->sampleDir = $this->testDir . 'samples' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->sampleDir . 'test.flac';

        $this->tmpFile = $this->tmpDir . 'test.flac';

        // Duplicate the version-controlled sample so it isn't modified.

        copy($this->sampleFile, $this->tmpFile);

        $this->{$this->fileType . 'ManipulatorCreator'} = app()->builder
            ->get($this->fileType . '_manipulator_creator');

        // Remove any existing tags from the temporary file before converting
        // (some tools preserve the tags when converting).

        $this->flacManipulatorCreator = app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);

        $this->flacManipulator->tagger->removeAllTags(
            $this->flacManipulator->getFile()
        );

        // Convert the master FLAC audio sample to ALAC.
        // Specify a unique destination file name.

        $this->{$this->fileType . 'Manipulator'} = $this->{$this->fileType . 'ManipulatorCreator'}
            ->create($this->tmpFile);

        $sample = $this->tmpDir . uniqid() . '.m4a';

        $this->flacManipulator->converter->toAlac(
            $this->flacManipulator->getFile(),
            $sample
        );

        // Use the newly-created file for testing.

        $this->{$this->fileType . 'Manipulator'} = $this->{$this->fileType . 'ManipulatorCreator'}
            ->create($sample);
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

        $testImage = file_get_contents($this->sampleDir . 'flac-logo.png');

        $this->assertEquals(
            $testImage,
            $fileDetails['comments']['picture'][0]['data']
        );

        $this->assertEquals(
            $testImage,
            $fileDetails['quicktime']['moov']['subatoms'][2]['subatoms'][0]['subatoms'][1]['subatoms'][1]['data']
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
            'title' => ['Test Song'],
            'artist' => ['Foobius Barius'],
            'year' => ['1981'],
            'comment' => ['All rights reserved.'],
            'album' => ['Test Title'],
            'tracknum' => ['1/1'],
            'genre' => ['Rock'],
        ];

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );

        $fileDetails = $this->{$this->fileType . 'Manipulator'}
            ->tagger
            ->getid3
            ->analyze($this->{$this->fileType . 'Manipulator'}->getFile());

        $keys = [
            'title' => $tagData['title'],
            'artist' => $tagData['artist'],
            'creation_date' => $tagData['year'],
            'comment' => $tagData['comment'],
            'album' => $tagData['album'],
            'track_number' => [$tagData['tracknum'][0]],
            'genre' => ['Rock'],
        ];

        $this->assertEquals(
            $keys,
            array_intersect_key($keys, $fileDetails['tags']['quicktime'])
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

        $this->assertArrayNotHasKey('picture', $fileDetails['comments']);

        $this->assertArrayNotHasKey(
            'subatoms',
            $fileDetails['quicktime']['moov']['subatoms'][2]['subatoms'][0]['subatoms'][1]
        );
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
                'artist',
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
            array_intersect_key(['artist' => ''], $fileDetails['tags']['quicktime'])
        );

        $this->assertEquals(
            [
                'artist' => $tagData['artist'],
            ],
            $fileDetails['tags']['quicktime']
        );
    }
}
