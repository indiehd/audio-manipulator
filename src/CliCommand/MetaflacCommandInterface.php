<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface MetaflacCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function output(string $outputFile): void;

    public function removeAll(): void;

    public function setTag(string $field, string $value): void;
}
