<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use IndieHD\AudioManipulator\CliCommand\FfmpegCommand;
use PHPUnit\Framework\TestCase;

class FfmpegCommandTest extends TestCase
{
    private FfmpegCommand $ffmpegCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->ffmpegCommand = new FfmpegCommand();
    }

    public function testBinaryCanBeSetFromEnvironment()
    {
        $this->assertNotEmpty(getenv('FFMPEG_BINARY'));
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
            $this->ffmpegCommand->getBinary().' -y -f flac -i test.flac -acodec alac test.alac',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->ffmpegCommand->input('test.flac');

        $this->assertEquals(
            $this->ffmpegCommand->getBinary()." -i 'test.flac'",
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesOutputCommandCorrectly()
    {
        $this->ffmpegCommand->output('test.alac');

        $this->assertEquals(
            $this->ffmpegCommand->getBinary()." 'test.alac'",
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesOverwriteOutputCommandCorrectly()
    {
        $this->ffmpegCommand->overwriteOutput();

        $this->assertEquals(
            $this->ffmpegCommand->getBinary().' -y',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesForceAudioCodecCommandCorrectly()
    {
        $this->ffmpegCommand->forceAudioCodec('alac');

        $this->assertEquals(
            $this->ffmpegCommand->getBinary().' -acodec alac',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesForceVideoCodecCommandCorrectly()
    {
        $this->ffmpegCommand->forceVideoCodec('copy');

        $this->assertEquals(
            $this->ffmpegCommand->getBinary().' -vcodec copy',
            $this->ffmpegCommand->asString()
        );
    }

    public function testItComposesDisableVideoCommandCorrectly()
    {
        $this->ffmpegCommand->disableVideo('alac');

        $this->assertEquals(
            $this->ffmpegCommand->getBinary().' -vn',
            $this->ffmpegCommand->asString()
        );
    }
}
