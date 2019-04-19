<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Tagging\TaggerInterface;

interface FlacTaggerInterface extends TaggerInterface
{
    public function removeAllTags(string $file): void;

    public function removeTags(string $file, array $tags): void;
}
