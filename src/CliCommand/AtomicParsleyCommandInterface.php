<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface AtomicParsleyCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): AtomicParsleyCommand;

    public function setArtwork(string $imageFile): AtomicParsleyCommand;

    public function overwrite(): AtomicParsleyCommand;

    public function deleteAll(): AtomicParsleyCommand;

    public function removeArtwork(): AtomicParsleyCommand;

    public function title(string $value): AtomicParsleyCommand;

    public function artist(string $value): AtomicParsleyCommand;

    public function year(string $value): AtomicParsleyCommand;

    public function comment(string $value): AtomicParsleyCommand;

    public function album(string $value): AtomicParsleyCommand;

    public function tracknum(string $value): AtomicParsleyCommand;

    public function genre(string $value): AtomicParsleyCommand;

    public function removeTags(array $tags): AtomicParsleyCommand;
}
