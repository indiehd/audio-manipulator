<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface AtomicParsleyCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function setArtwork(string $imageFile): void;

    public function overwrite(): void;

    public function deleteAll(): void;

    public function removeArtwork(): void;

    public function title(string $value): void;

    public function artist(string $value): void;

    public function year(string $value): void;

    public function comment(string $value): void;

    public function album(string $value): void;

    public function tracknum(string $value): void;

    public function genre(string $value): void;

    public function removeTags(array $tags): void;
}
