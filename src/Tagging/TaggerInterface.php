<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerInterface
{
    public function writeTags(array $tagData);

    public function removeTags(array $data);

    public function writeArtwork(string $imagePath);

    public function removeArtwork();
}
