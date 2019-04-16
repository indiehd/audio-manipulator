<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function writeTags(string $file, array $tagData, string $coverFile = null): array;

    public function removeAllTags(string $file): bool;

    public function removeTags(array $data): bool;

    public function writeArtwork(string $audioFile, string $imageFile): bool;

    public function removeArtwork(string $file): bool;
}
