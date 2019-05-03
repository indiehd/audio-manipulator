<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TagVerifierInterface
{
    public function verify(string $file, array $tagData, array $fieldMappings = null): void;
}
