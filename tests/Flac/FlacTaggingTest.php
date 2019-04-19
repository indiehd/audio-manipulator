<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use function IndieHD\AudioManipulator\app;

use IndieHD\AudioManipulator\Tests\Tagging\TaggingTest;

class FlacTaggingTest extends TaggingTest
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
        $this->setFileType('flac');

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
    }

    protected function setFileType(string $type): void
    {
        $this->fileType = $type;
    }

    /*
    public function testItThrowsExceptionWhenProcessFails()
    {
        $this->expectException(ProcessFailedException::class);

        $this->{$this->fileType . 'Manipulator'}->tagger->command->setBinary('non-existent-binary-path');

        $this->{$this->fileType . 'Manipulator'}->writeTags([]);
    }
    */

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
            $fileDetails['flac']['PICTURE'][0]['data']
        );
    }

    protected function removeAllTags()
    {
        $this->{$this->fileType . 'Manipulator'}->tagger->removeAllTags(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );
    }
}
