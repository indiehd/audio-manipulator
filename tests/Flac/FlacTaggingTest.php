<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;

class FlacTaggingTest extends TestCase
{
    private $testDir;

    private $tmpDir;

    private $sampleDir;

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

        $this->sampleDir = $this->testDir . 'samples' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->sampleDir . 'test.flac';

        $this->tmpFile = $this->tmpDir . 'test.flac';

        copy($this->sampleFile, $this->tmpFile);

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);
    }

    /**
     * Ensure that a FLAC file can be tagged with metadata.
     *
     * @return void
     */
    public function testItCanTagFlacFile()
    {
        $tagData = [
            'title' => ['Test Song'],
            'artist' => ['Foobius Barius'],
            'date' => ['1981'],
            'description' => ['All rights reserved.'],
            'album' => ['Test Title'],
            'discnumber' => ['1'],
            'tracknumber' => ['1'],
            'genre' => ['Rock'],
        ];

        $this->flacManipulator->writeTags(
            $tagData
        );

        $fileDetails = $this->flacManipulator
            ->tagger
            ->getid3
            ->analyze($this->flacManipulator->getFile());

        $this->assertEquals(
            [
                'title' => $tagData['title'],
                'artist' => $tagData['artist'],
                'date' => $tagData['date'],
                'description' => $tagData['description'],
                'album' => $tagData['album'],
                'discnumber' => ['1'],
                'tracknumber' => [$tagData['tracknumber'][0]],
                'genre' => ['Rock'],
            ],
            $fileDetails['tags']['vorbiscomment']
        );
    }

    public function testItThrowsExceptionWhenFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create('foo.bar');

        $this->flacManipulator->writeTags(
            []
        );
    }

    public function testItThrowsExceptionWhenProcessFails()
    {
        $this->expectException(ProcessFailedException::class);

        $this->flacManipulator->tagger->command->setBinary('non-existent-binary-path');

        $this->flacManipulator->writeTags([]);
    }

    private function removeAllTags()
    {
        $this->flacManipulator->tagger->removeAllTags(
            $this->flacManipulator->getFile()
        );
    }

    private function removeArtwork()
    {
        $this->flacManipulator->tagger->removeArtwork(
            $this->flacManipulator->getFile()
        );
    }

    private function embedArtwork()
    {
        $this->removeArtwork();

        $this->flacManipulator->tagger->writeArtwork(
            $this->flacManipulator->getFile(),
            $this->sampleDir . 'flac-logo.gif'
        );
    }

    public function testItCanEmbedArtwork()
    {
        $this->embedArtwork();

        $fileDetails = $this->flacManipulator->tagger->getid3->analyze(
            $this->flacManipulator->getFile()
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

    public function testItCanRemoveArtworkFromFlacFile()
    {
        $this->removeAllTags();

        $this->embedArtwork();

        $this->removeArtwork();

        $fileDetails = $this->flacManipulator->tagger->getid3->analyze(
            $this->flacManipulator->getFile()
        );

        $this->assertArrayNotHasKey('comments', $fileDetails);

        $this->assertArrayNotHasKey('PICTURE', $fileDetails['flac']);
    }

    public function testItCanRemoveTagsFromFlacFile()
    {
        $this->removeAllTags();

        $this->flacManipulator->writeTags(
            [
                'title' => ['Foo'],
            ]
        );

        $this->flacManipulator->removeTags(
            [
                'title',
            ]
        );

        $fileDetails = $this->flacManipulator->tagger->getid3->analyze(
            $this->flacManipulator->getFile()
        );

        $this->assertArrayNotHasKey('tags', $fileDetails);
    }
}
