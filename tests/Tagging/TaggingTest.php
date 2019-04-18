<?php

namespace IndieHD\AudioManipulator\Tests\Tagging;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use function \IndieHD\AudioManipulator\app;

use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;

abstract class TaggingTest extends TestCase
{
    protected $fileType;

    protected $sampleDir;

    abstract protected function setFileType(string $type): void;

    /**
     * Ensure that a FLAC file can be tagged with metadata.
     *
     * @return void
     */
    public function testItCanTagFile()
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

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );

        $fileDetails = $this->{$this->fileType . 'Manipulator'}
            ->tagger
            ->getid3
            ->analyze($this->{$this->fileType . 'Manipulator'}->getFile());

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

        $this->{$this->fileType . 'Manipulator'} = $this->{$this->fileType . 'ManipulatorCreator'}
            ->create('foo.bar');

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            []
        );
    }

    public function testItThrowsExceptionWhenProcessFails()
    {
        $this->expectException(ProcessFailedException::class);

        $this->{$this->fileType . 'Manipulator'}->tagger->command->setBinary('non-existent-binary-path');

        $this->{$this->fileType . 'Manipulator'}->writeTags([]);
    }

    protected function removeAllTags()
    {
        $this->{$this->fileType . 'Manipulator'}->tagger->removeAllTags(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );
    }

    protected function removeArtwork()
    {
        $this->{$this->fileType . 'Manipulator'}->tagger->removeArtwork(
            $this->{$this->fileType . 'Manipulator'}->getFile()
        );
    }

    protected function embedArtwork()
    {
        $this->removeArtwork();

        $this->{$this->fileType . 'Manipulator'}->tagger->writeArtwork(
            $this->{$this->fileType . 'Manipulator'}->getFile(),
            $this->sampleDir . 'flac-logo.gif'
        );
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
            $fileDetails['flac']['PICTURE'][0]['data']
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

        $this->assertArrayNotHasKey('PICTURE', $fileDetails['flac']);
    }

    public function testItCanRemoveTagsFromFile()
    {
        $this->removeAllTags();

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            [
                'title' => ['Foo'],
            ]
        );

        $this->{$this->fileType . 'Manipulator'}->removeTags(
            [
                'title',
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
            'title' => ['﻿ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ'],
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
                'title' => $tagData['title'],
            ],
            $fileDetails['tags']['vorbiscomment']
        );
    }

    public function testExceptionIsThrownWhenTagsCannotBeVerifiedAfterWriting()
    {
        $this->expectException(AudioTaggerException::class);

        // Set an inappropriate encoding, which will cause the tag to be
        // written incorrectly.

        $this->{$this->fileType . 'Manipulator'}->tagger->setEnv(['LC_ALL' => 'en_US.iso-8859-1']);

        $tagData = [
            'title' => ['﻿ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ'],
        ];

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );
    }
}
