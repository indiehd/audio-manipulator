<?php

namespace IndieHD\AudioManipulator\Converting;

interface ConverterInterface
{
    public function setSupportedOutputFormats(array $supportedOutputFormats): void;

    public function writeFile(string $inputFile, string $outputFile);
}
