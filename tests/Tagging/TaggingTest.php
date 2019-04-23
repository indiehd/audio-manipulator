<?php

namespace IndieHD\AudioManipulator\Tests\Tagging;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;

abstract class TaggingTest extends TestCase
{
    protected $fileType;

    protected $sampleDir;

    abstract protected function setFileType(string $type): void;

    /**
     * Ensure that the file can be tagged with metadata.
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

    abstract public function testItCanEmbedArtwork();

    abstract public function testItCanRemoveArtworkFromFile();

    abstract public function testItCanRemoveTagsFromFile();

    abstract public function testItCanWriteUtf8TagValuesAccurately();

    /*
    public function testExceptionIsThrownWhenTagsCannotBeVerifiedAfterWriting()
    {
        $this->expectException(AudioTaggerException::class);

        // Set an inappropriate encoding, which will cause the tag to be
        // written incorrectly.

        #$this->{$this->fileType . 'Manipulator'}->tagger->setEnv(['LC_ALL' => 'en_US.iso-8859-1']);

        $tagData = [
            'artist' => ['﻿ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ'],
        ];

        $this->{$this->fileType . 'Manipulator'}->writeTags(
            $tagData
        );
    }
    */
}
