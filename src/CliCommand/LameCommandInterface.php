<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface LameCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function output(string $outputFile): void;
}
