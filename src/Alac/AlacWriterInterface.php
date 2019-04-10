<?php

namespace IndieHD\AudioManipulator\Alac;

interface AlacWriterInterface
{
    public function toAlac(string $inputFile, string $outputFile): array;
}
