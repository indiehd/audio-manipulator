<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface AtomicParsleyCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;
}
