<?php

namespace IndieHD\AudioManipulator\Flac;

interface FlacWriterInterface
{
    public function toFlac(string $inputFile, string $outputFile): array;
}
