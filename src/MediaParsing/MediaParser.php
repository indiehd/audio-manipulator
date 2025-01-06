<?php

namespace IndieHD\AudioManipulator\MediaParsing;

use getID3;

class MediaParser implements MediaParserInterface
{
    protected getID3 $parser;

    public function __construct()
    {
        $this->parser = new getID3();
    }

    public function analyze(string $file)
    {
        return $this->parser->analyze($file);
    }
}
