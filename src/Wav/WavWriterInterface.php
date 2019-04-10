<?php

namespace IndieHD\AudioManipulator\Wav;

interface WavWriterInterface
{
    public function toWav(string $inputFile, string $outputFile): array;
}
