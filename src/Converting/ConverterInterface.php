<?php

namespace IndieHD\AudioManipulator\Converting;

interface ConverterInterface
{
    public function writeFile(string $inputFile, string $outputFile);

    public function applyEffect($effect);
}
