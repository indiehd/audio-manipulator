<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use IndieHD\AudioManipulator\CliCommand\Mid3v2Command;
use PHPUnit\Framework\TestCase;

class Mid3v2CommandTest extends TestCase
{
    private $command;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->command = new Mid3v2Command();
    }

    public function testBinaryCanBeSetFromEnvironment()
    {
        $this->assertNotEmpty(getenv('MID3V2_BINARY'));
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

        $this->command->addArgument('options', '--quiet');

        $this->command->addArgument('infile', 'test.mp3');

        $this->assertEquals(
            $this->command->getBinary().' --quiet test.mp3',
            $this->command->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->command->input('test.mp3');

        $this->assertEquals(
            $this->command->getBinary()." 'test.mp3'",
            $this->command->asString()
        );
    }

    public function testItComposesQuietCommandCorrectly()
    {
        $this->command->quiet();

        $this->assertEquals(
            $this->command->getBinary().' --quiet',
            $this->command->asString()
        );
    }

    public function testItComposesSongCommandCorrectly()
    {
        $this->command->song('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --song='Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesArtistCommandCorrectly()
    {
        $this->command->artist('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --artist='Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesYearCommandCorrectly()
    {
        $this->command->year('1981');

        $this->assertEquals(
            $this->command->getBinary()." --year='1981'",
            $this->command->asString()
        );
    }

    public function testItComposesCommentCommandCorrectly()
    {
        $this->command->comment('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --comment=0:'Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesAlbumCommandCorrectly()
    {
        $this->command->album('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --album='Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesTrackCommandCorrectly()
    {
        $this->command->track('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --track='Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesGenreCommandCorrectly()
    {
        $this->command->genre('Foo');

        $this->assertEquals(
            $this->command->getBinary()." --genre='Foo'",
            $this->command->asString()
        );
    }

    public function testItComposesDeleteAllCommandCorrectly()
    {
        $this->command->deleteAll();

        $this->assertEquals(
            $this->command->getBinary().' --delete-all',
            $this->command->asString()
        );
    }

    public function testItComposesPictureCommandCorrectly()
    {
        $this->command->picture('test.jpg', 'test.mp3');

        $this->assertEquals(
            $this->command->getBinary()." --APIC 'test.jpg' 'test.mp3'",
            $this->command->asString()
        );
    }

    public function testItComposesRemoveArtworkCommandCorrectly()
    {
        $this->command->removeArtwork();

        $this->assertEquals(
            $this->command->getBinary().' --delete-frames=APIC',
            $this->command->asString()
        );
    }

    public function testItComposesRemoveTagsCommandCorrectly()
    {
        $this->command->removeTags(['Foo', 'Bar']);

        $this->assertEquals(
            $this->command->getBinary()." --delete-frames='Foo,Bar'",
            $this->command->asString()
        );
    }
}
