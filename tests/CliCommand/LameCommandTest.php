<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\LameCommand;

class LameCommandTest extends TestCase
{
    private $command;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->command = new LameCommand();
    }

    public function testBinaryCanBeSetFromEnvironment()
    {
        $this->assertNotEmpty(getenv('LAME_BINARY'));
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->command->getName());
        $this->assertIsString($this->command->getBinary());
        $this->assertIsArray($this->command->getCommandParts());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCommandPartIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->addArgument('foo', 'bar');
    }

    public function testItComposesCommandCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->command->addArgument('infile', 'test.flac');

        $this->command->addArgument('outfile', 'test.mp3');

        $this->command->addArgument('options', '-b 320');

        $this->assertEquals(
            $this->command->getBinary() . ' -b 320 test.flac test.mp3',
            $this->command->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->command->input('test.flac');

        $this->assertEquals(
            $this->command->getBinary() . " 'test.flac'",
            $this->command->asString()
        );
    }

    public function testItComposesOutputCommandCorrectly()
    {
        $this->command->output('test.mp3');

        $this->assertEquals(
            $this->command->getBinary() . " 'test.mp3'",
            $this->command->asString()
        );
    }

    public function testItComposesQuietCommandCorrectly()
    {
        $this->command->quiet();

        $this->assertEquals(
            $this->command->getBinary() . ' --quiet',
            $this->command->asString()
        );
    }

    public function testItComposesEnableAndForceLameTagCommandCorrectly()
    {
        $this->command->enableAndForceLameTag();

        $this->assertEquals(
            $this->command->getBinary() . ' -T',
            $this->command->asString()
        );
    }

    public function testItComposesNoReplayGainCommandCorrectly()
    {
        $this->command->noReplayGain();

        $this->assertEquals(
            $this->command->getBinary() . ' --noreplaygain',
            $this->command->asString()
        );
    }

    public function testItComposesQualityCommandCorrectly()
    {
        $this->command->quality(0);

        $this->assertEquals(
            $this->command->getBinary() . ' --q 0',
            $this->command->asString()
        );
    }

    public function testItComposesResampleCommandCorrectly()
    {
        $this->command->resample(44);

        $this->assertEquals(
            $this->command->getBinary() . ' --resample 44',
            $this->command->asString()
        );
    }

    public function testItComposesBitwidthCommandCorrectly()
    {
        $this->command->bitwidth(16);

        $this->assertEquals(
            $this->command->getBinary() . ' --bitwidth 16',
            $this->command->asString()
        );
    }

    public function testItComposesCbrCommandCorrectly()
    {
        $this->command->cbr();

        $this->assertEquals(
            $this->command->getBinary() . ' --cbr',
            $this->command->asString()
        );
    }

    public function testItComposesBitrateCommandCorrectly()
    {
        $this->command->bitrate(128);

        $this->assertEquals(
            $this->command->getBinary() . ' -b 128',
            $this->command->asString()
        );
    }

    public function testItComposesAbrCommandCorrectly()
    {
        $this->command->abr();

        $this->assertEquals(
            $this->command->getBinary() . ' --abr',
            $this->command->asString()
        );
    }

    public function testItComposesVbrCommandCorrectly()
    {
        $this->command->vbr(4);

        $this->assertEquals(
            $this->command->getBinary() . ' --vbr-new -V 4',
            $this->command->asString()
        );
    }

    public function testItComposesSetTitleCommandCorrectly()
    {
        $this->command->setTitle('Foo');

        $this->assertEquals(
            $this->command->getBinary() . " --tt 'Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesSetArtistCommandCorrectly()
    {
        $this->command->setArtist('Foo');

        $this->assertEquals(
            $this->command->getBinary() . " --ta 'Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesSetYearCommandCorrectly()
    {
        $this->command->setYear('1981');

        $this->assertEquals(
            $this->command->getBinary() . " --ty '1981'",
            $this->command->asString()
        );
    }

    public function testItComposesCommentCommandCorrectly()
    {
        $this->command->setComment('Foo');

        $this->assertEquals(
            $this->command->getBinary() . " --tc 'Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesSetAlbumCommandCorrectly()
    {
        $this->command->setAlbum('Foo');

        $this->assertEquals(
            $this->command->getBinary() . " --tl 'Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesSetTracknumberCommandCorrectly()
    {
        $this->command->setTracknumber(1);

        $this->assertEquals(
            $this->command->getBinary() . " --tn '1'",
            $this->command->asString()
        );
    }

    public function testItComposesSetGenreCommandCorrectly()
    {
        $this->command->setGenre('Foo');

        $this->assertEquals(
            $this->command->getBinary() . " --tg 'Foo'",
            $this->command->asString()
        );
    }
}
