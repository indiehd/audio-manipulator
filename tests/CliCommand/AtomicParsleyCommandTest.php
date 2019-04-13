<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\AtomicParsleyCommand;

class AtomicParsleyCommandTest extends TestCase
{
    private $atomicParsleyCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->atomicParsleyCommand = new AtomicParsleyCommand();
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->atomicParsleyCommand->getName());
        $this->assertIsString($this->atomicParsleyCommand->getBinary());
        $this->assertIsArray($this->atomicParsleyCommand->getCommandParts());
    }

    public function testItThrowsInvalidArgumentExceptionWhenCommandPartIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->atomicParsleyCommand->addArgument('foo', 'bar');
    }

    public function testItComposesCommandCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->atomicParsleyCommand->addArgument('infile', 'test.alac');

        $this->atomicParsleyCommand->addArgument('options', '-T 1');

        $this->assertEquals(
            $this->atomicParsleyCommand->getBinary() . ' test.alac -T 1',
            $this->atomicParsleyCommand->asString()
        );
    }

    public function testItComposesInputCommandCorrectly()
    {
        $this->atomicParsleyCommand->input('test.alac');

        $this->assertEquals(
            $this->atomicParsleyCommand->getBinary() . " 'test.alac'",
            $this->atomicParsleyCommand->asString()
        );
    }

    public function testItComposesSetArtworkCommandCorrectly()
    {
        $this->atomicParsleyCommand->setArtwork('foo.jpg');

        $this->assertEquals(
            $this->atomicParsleyCommand->getBinary() . " --artwork 'foo.jpg'",
            $this->atomicParsleyCommand->asString()
        );
    }

    public function testItComposesOverwriteCommandCorrectly()
    {
        $this->atomicParsleyCommand->overwrite();

        $this->assertEquals(
            $this->atomicParsleyCommand->getBinary() . ' --overWrite',
            $this->atomicParsleyCommand->asString()
        );
    }
}
