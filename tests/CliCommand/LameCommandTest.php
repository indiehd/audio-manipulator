<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\LameCommand;

class LameCommandTest extends TestCase
{
    private $lameCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->lameCommand = new LameCommand();
    }

    public function testBinaryCanBeSetFromEnvironment()
    {
        $this->assertEquals('lame', getenv('LAME_BINARY'));
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->lameCommand->getName());
        $this->assertIsString($this->lameCommand->getBinary());
        $this->assertIsArray($this->lameCommand->getCommandParts());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCommandPartIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->lameCommand->addArgument('foo', 'bar');
    }

    public function testItComposesCommandCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->lameCommand->addArgument('infile', 'test.flac');

        $this->lameCommand->addArgument('outfile', 'test.mp3');

        $this->lameCommand->addArgument('options', '-b 320');

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' -b 320 test.flac test.mp3',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->lameCommand->input('test.flac');

        $this->assertEquals(
            $this->lameCommand->getBinary() . " 'test.flac'",
            $this->lameCommand->asString()
        );
    }

    public function testItComposesOutputCommandCorrectly()
    {
        $this->lameCommand->output('test.mp3');

        $this->assertEquals(
            $this->lameCommand->getBinary() . " 'test.mp3'",
            $this->lameCommand->asString()
        );
    }

    public function testItComposesQuietCommandCorrectly()
    {
        $this->lameCommand->quiet();

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --quiet',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesEnableAndForceLameTagCommandCorrectly()
    {
        $this->lameCommand->enableAndForceLameTag();

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' -T',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesNoReplayGainCommandCorrectly()
    {
        $this->lameCommand->noReplayGain();

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --noreplaygain',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesQualityCommandCorrectly()
    {
        $this->lameCommand->quality(0);

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --q 0',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesResampleCommandCorrectly()
    {
        $this->lameCommand->resample(44);

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --resample 44',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesBitwidthCommandCorrectly()
    {
        $this->lameCommand->bitwidth(16);

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --bitwidth 16',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesCbrCommandCorrectly()
    {
        $this->lameCommand->cbr();

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --cbr',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesBitrateCommandCorrectly()
    {
        $this->lameCommand->bitrate(128);

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' -b 128',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesAbrCommandCorrectly()
    {
        $this->lameCommand->abr();

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --abr',
            $this->lameCommand->asString()
        );
    }

    public function testItComposesVbrCommandCorrectly()
    {
        $this->lameCommand->vbr(4);

        $this->assertEquals(
            $this->lameCommand->getBinary() . ' --vbr-new -V 4',
            $this->lameCommand->asString()
        );
    }
}
