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
}
