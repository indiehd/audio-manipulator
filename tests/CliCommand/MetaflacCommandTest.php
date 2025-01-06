<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use IndieHD\AudioManipulator\CliCommand\MetaflacCommand;
use PHPUnit\Framework\TestCase;

class MetaflacCommandTest extends TestCase
{
    private MetaflacCommand $metaflacCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->metaflacCommand = new MetaflacCommand();
    }

    public function testBinaryCanBeSetFromEnvironment()
    {
        $this->assertNotEmpty(getenv('METAFLAC_BINARY'));
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->metaflacCommand->getName());
        $this->assertIsString($this->metaflacCommand->getBinary());
        $this->assertIsArray($this->metaflacCommand->getCommandParts());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCommandPartIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->metaflacCommand->addArgument('foo', 'bar');
    }

    public function testItComposesCommandCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->metaflacCommand->addArgument('operations', '--remove');

        $this->metaflacCommand->addArgument('flacfile-out', 'test.flac');

        $this->metaflacCommand->addArgument('flacfile-in', 'test.flac');

        $this->metaflacCommand->addArgument('options', '--no-utf8-convert');

        $this->assertEquals(
            $this->metaflacCommand->getBinary().' --no-utf8-convert --remove test.flac test.flac',
            $this->metaflacCommand->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->metaflacCommand->input('test.flac');

        $this->assertEquals(
            $this->metaflacCommand->getBinary()." 'test.flac'",
            $this->metaflacCommand->asString()
        );
    }

    public function testItComposesOutputCommandCorrectly()
    {
        $this->metaflacCommand->output('test.flac');

        $this->assertEquals(
            $this->metaflacCommand->getBinary()." 'test.flac'",
            $this->metaflacCommand->asString()
        );
    }

    public function testItComposesRemoveAllCorrectly()
    {
        $this->metaflacCommand->removeAll();

        $this->assertEquals(
            $this->metaflacCommand->getBinary().' --remove-all',
            $this->metaflacCommand->asString()
        );
    }

    public function testItComposeSetTagCorrectly()
    {
        $this->metaflacCommand->setTag('Artist', 'Foobius Barius');

        $this->assertEquals(
            $this->metaflacCommand->getBinary()." --set-tag='Artist'='Foobius Barius'",
            $this->metaflacCommand->asString()
        );
    }
}
