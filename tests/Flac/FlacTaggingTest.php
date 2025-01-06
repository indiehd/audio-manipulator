<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use IndieHD\AudioManipulator\Flac\FlacManipulator;
use IndieHD\AudioManipulator\Flac\FlacManipulatorCreatorInterface;
use function IndieHD\AudioManipulator\app;
use IndieHD\AudioManipulator\Tests\Tagging\TaggingTest;

class FlacTaggingTest extends TaggingTest
{
    private string $testDir;

    private string $tmpDir;

    private string $sampleFile;

    private string $tmpFile;

    public FlacManipulatorCreatorInterface $flacManipulatorCreator;

    public FlacManipulator $flacManipulator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->setFileType('flac');

        // Define filesystem paths for use in testing.

        $this->testDir = __DIR__.DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir.'storage'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;

        $this->sampleDir = $this->testDir.'samples'.DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->sampleDir.'test.flac';

        $this->tmpFile = $this->tmpDir.'test.flac';

        // Duplicate the version-controlled sample so it isn't modified.

        copy($this->sampleFile, $this->tmpFile);

        // Use the newly-created file for testing.

        $this->flacManipulatorCreator = app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);
    }

    protected function setFileType(string $type): void
    {
        $this->fileType = $type;
    }

    public function testItCanEmbedArtwork()
    {
        $this->embedArtwork();

        $fileDetails = $this->{$this->fileType.'Manipulator'}->tagger->tagVerifier->getid3->analyze(
            $this->{$this->fileType.'Manipulator'}->getFile()
        );

        $testImage = file_get_contents($this->sampleDir.'flac-logo.png');

        $this->assertEquals(
            $testImage,
            $fileDetails['comments']['picture'][0]['data']
        );

        $this->assertEquals(
            $testImage,
            $fileDetails['flac']['PICTURE'][0]['data']
        );
    }

    protected function removeAllTags()
    {
        $this->{$this->fileType.'Manipulator'}->tagger->removeAllTags(
            $this->{$this->fileType.'Manipulator'}->getFile()
        );
    }

    public function testItCanRemoveArtworkFromFile()
    {
        $this->removeAllTags();

        $this->embedArtwork();

        $this->removeArtwork();

        $fileDetails = $this->{$this->fileType.'Manipulator'}->tagger->tagVerifier->getid3->analyze(
            $this->{$this->fileType.'Manipulator'}->getFile()
        );

        $this->assertArrayNotHasKey('comments', $fileDetails);

        $this->assertArrayNotHasKey('PICTURE', $fileDetails['flac']);
    }

    public function testItCanRemoveTagsFromFile()
    {
        $this->removeAllTags();

        $this->{$this->fileType.'Manipulator'}->writeTags(
            [
                'artist' => ['Foo'],
            ]
        );

        $this->{$this->fileType.'Manipulator'}->removeTags(
            [
                'artist',
            ]
        );

        $fileDetails = $this->{$this->fileType.'Manipulator'}->tagger->tagVerifier->getid3->analyze(
            $this->{$this->fileType.'Manipulator'}->getFile()
        );

        $this->assertArrayNotHasKey('tags', $fileDetails);
    }

    public function testItCanWriteUtf8TagValuesAccurately()
    {
        $tagData = [
            'artist' => ['ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ'],
        ];

        $this->{$this->fileType.'Manipulator'}->writeTags(
            $tagData
        );

        $fileDetails = $this->{$this->fileType.'Manipulator'}
            ->tagger
            ->tagVerifier
            ->getid3
            ->analyze($this->{$this->fileType.'Manipulator'}->getFile());

        $this->assertEquals(
            [
                'artist' => $tagData['artist'],
            ],
            $fileDetails['tags']['vorbiscomment']
        );

        $this->assertEquals(
            [
                'artist' => $tagData['artist'],
            ],
            $fileDetails['flac']['comments']
        );

        $this->assertEquals(
            $tagData['artist'],
            $fileDetails['flac']['VORBIS_COMMENT']['comments']['artist']
        );
    }
}
