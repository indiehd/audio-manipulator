<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function writeTags(string $file, array $tagData, string $coverFile = null): array;

    public function removeTags(array $data);

    public function writeArtwork(string $imagePath);

    public function removeArtwork();
}
