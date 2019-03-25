<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

class TranscodingTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->transcoder = app()->builder->get('transcoder');
        
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;
    }

    public function testTranscodingFlacToMp3Succeeds()
    {
        $this->testDir =

        $this->assertTrue($this->transcoder->transcode(
            $this->testDir . 'foo.flac',
            $this->testDir . 'foo.mp3'
        )['result']);
    }
    
    public function testConvertingWavToFlacSucceeds()
    {
        $this->assertIsArray($this->transcoder->convertWavToFlac(
            $this->testDir . 'foo.wav',
            $this->testDir . 'foo.flac'
        )['result']);
    }
    
    public function testTranscodingWavToMp3Succeeds()
    {
        $this->assertTrue($this->transcoder->wavToMp3(
            $this->testDir . 'foo.wav',
            $this->testDir . 'foo.mp3',
            'cbr',
            128
        )['result']);
    }
    
    public function testConvertingFlacToAlacSucceeds()
    {
        $this->assertTrue($this->transcoder->transcodeFlacToAlac(
            $this->testDir . 'foo.flac'
        )['result']);
    }
}
