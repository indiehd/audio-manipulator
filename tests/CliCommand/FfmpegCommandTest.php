<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\FfmpegCommand;

class FfmpegCommandTest extends TestCase
{
    private $ffmpegCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->ffmpegCommand = new FfmpegCommand();
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->ffmpegCommand->getName());
        $this->assertIsString($this->ffmpegCommand->getBinary());
        $this->assertIsArray($this->ffmpegCommand->getCommandParts());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCommandPartIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->ffmpegCommand->addArgument('foo', 'bar');
    }

    public function testItComposesCommandCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->ffmpegCommand->addArgument('infile', '-i test.flac');

        $this->ffmpegCommand->addArgument('outfile', 'test.alac');

        $this->ffmpegCommand->addArgument('outfile-options', '-acodec alac');

        $this->ffmpegCommand->addArgument('infile-options', '-f flac');

        $this->ffmpegCommand->addArgument('options', '-y');

        $this->assertEquals(
            'ffmpeg -y -f flac -i test.flac -acodec alac test.alac',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->ffmpegCommand->input('test.flac');

        $this->assertEquals(
            "ffmpeg -i 'test.flac'",
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesOutputCommandCorrectly()
    {
        $this->ffmpegCommand->output('test.alac');

        $this->assertEquals(
            "ffmpeg 'test.alac'",
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesOverwriteOutputCommandCorrectly()
    {
        $this->ffmpegCommand->overwriteOutput();

        $this->assertEquals(
            'ffmpeg -y',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesForceAudioCodecCommandCorrectly()
    {
        $this->ffmpegCommand->forceAudioCodec('alac');

        $this->assertEquals(
            'ffmpeg -acodec alac',
            $this->ffmpegCommand->asString()
        );
    }
}
