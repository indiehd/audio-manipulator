<?php

namespace IndieHD\AudioManipulator;

use PHPUnit\Framework\TestCase;

use IndieHD\AudioManipulator\CliCommand\SoxCommand;

class CliCommandTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->soxCommand = new SoxCommand();
    }

    public function testCommandIsComposedCorrectly()
    {
        $this->soxCommand->addArgument('gopts', '--single-threaded');

        $this->assertEquals('sox --single-threaded', implode(' ', $this->soxCommand->compose()));
    }
}
