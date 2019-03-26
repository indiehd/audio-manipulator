<?php

namespace IndieHD\AudioManipulator\MediaParsing;

interface MediaParserInterface
{
    public function analyze(string $file);
}
