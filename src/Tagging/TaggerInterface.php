<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function writeTags(string $file, array $tagData): void;

    public function removeAllTags(string $file): void;

    public function removeTags(string $file, array $tags): void;

    public function writeArtwork(string $audioFile, string $imageFile): void;

    public function removeArtwork(string $file): void;
}
