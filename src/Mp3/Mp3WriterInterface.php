<?php

namespace IndieHD\AudioManipulator\Mp3;

interface Mp3WriterInterface
{
    public function toMp3(string $inputFile, string $outputFile): array;
}
