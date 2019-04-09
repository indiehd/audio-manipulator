<?php

namespace IndieHD\AudioManipulator\Tests\CliCommand;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\SoxCommand;

class SoxCommandTest extends TestCase
{
    private $soxCommand;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->soxCommand = new SoxCommand();
    }

    public function testWhenInstantiatedRequiredPropertiesAreSet()
    {
        $this->assertIsString($this->soxCommand->getName());
        $this->assertIsString($this->soxCommand->getBinary());
        $this->assertIsArray($this->soxCommand->getCommandParts());
    }

    public function testCommandIsComposedCorrectly()
    {
        // These commands are purposely placed in a non-sequential order to prove
        // that the composition order is not contingent upon the order in which
        // the the arguments are added.

        $this->soxCommand->addArgument('effopt', 'q 0.5 1 0.5');

        $this->soxCommand->addArgument('infile', 'test.flac');

        $this->soxCommand->addArgument('outfile', 'test.wav');

        $this->soxCommand->addArgument('fopts-out', '--no-clobber');

        $this->soxCommand->addArgument('fopts-in', '--channels 2');

        $this->soxCommand->addArgument('effect', 'fade');

        $this->soxCommand->addArgument('gopts', '--single-threaded');

        $this->assertEquals(
            'sox --single-threaded --channels 2 test.flac --no-clobber test.wav fade q 0.5 1 0.5',
            implode(' ', $this->soxCommand->compose())
        );
    }

    public function testFadeCommandIsComposedCorrectly()
    {
        $this->soxCommand->addArgument('infile', 'test.flac');

        $this->soxCommand->addArgument('outfile', 'faded.flac');

        $this->soxCommand->fade('q', 0.5, 1, 0.5);

        $this->assertEquals(
            'sox test.flac faded.flac fade q 0.5 1 0.5',
            implode(' ', $this->soxCommand->compose())
        );
    }
}
