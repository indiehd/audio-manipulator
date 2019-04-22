<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function setEnv(array $env): void;

    public function writeTags(string $file, array $tagData): void;

    public function writeArtwork(string $audioFile, string $imageFile): void;

    public function removeArtwork(string $file): void;
}
