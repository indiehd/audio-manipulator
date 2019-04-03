<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\Converting\ConverterInterface;

class Mp3Converter implements ConverterInterface
{
    public function __construct()
    {
        $this->setSupportedOutputFormats([]);
    }

    public function setSupportedOutputFormats(array $supportedOutputFormats): void
    {
        $this->supportedOutputFormats = $supportedOutputFormats;
    }

    public function writeFile(string $inputFile, string $outputFile)
    {
        // TODO: Implement writeFile() method.
    }

    public function applyEffect($effect)
    {
        // TODO: Implement applyEffect() method.
    }
}
