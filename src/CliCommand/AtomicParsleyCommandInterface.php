<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface AtomicParsleyCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function setArtwork(string $imageFile): void;

    public function overwrite(): void;
}
