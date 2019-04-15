<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;

class FlacTaggingTest extends TestCase
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
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir . 'storage' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->testDir . 'samples' . DIRECTORY_SEPARATOR . 'test.flac';

        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'test.flac';

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

    /*
    public function testItThrowExceptionWhenProcessFails()
    {
        $this->expectException(ProcessFailedException::class);

        $_ENV['METAFLAC_BINARY'] = 'foo';

        $this->flacManipulator->writeTags(
            []
        );
    }
    */
}
