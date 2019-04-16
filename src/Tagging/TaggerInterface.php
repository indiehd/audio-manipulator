<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function writeTags(string $file, array $tagData, string $coverFile = null): array;

    public function removeAllTags(string $file): void;

    public function removeTags(array $data): void;

    public function writeArtwork(string $audioFile, string $imageFile): void;

    public function removeArtwork(string $file): void;
}
