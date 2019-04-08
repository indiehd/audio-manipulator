<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface SoxCommandInterface extends CliCommandInterface
{
    public function singleThreaded(): void;

    public function verbosity(int $level): void;

    public function input(string $inputFile): void;

    public function channels(int $channels): void;

    public function output(string $outputFile): void;
}
