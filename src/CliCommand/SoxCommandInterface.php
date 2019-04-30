<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface SoxCommandInterface extends CliCommandInterface
{
    public function singleThreaded(): SoxCommand;

    public function verbosity(int $level): SoxCommand;

    public function input(string $inputFile): SoxCommand;

    public function channels(int $channels): SoxCommand;

    public function output(string $outputFile): SoxCommand;

    public function fade(
        string $type = null,
        float $fadeInLength,
        float $stopPosition = null,
        float $fadeOutLength = null
    ): SoxCommand;
}
