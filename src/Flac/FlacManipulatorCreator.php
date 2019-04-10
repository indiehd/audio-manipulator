<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class FlacManipulatorCreator implements FlacManipulatorCreatorInterface
{
    public function __construct(
        ConverterInterface $converter,
        TaggerInterface $tagger
    ) {
        $this->converter = $converter;
        $this->tagger = $tagger;
    }

    public function create(string $file): FlacManipulator
    {
        $flacManipulator = new FlacManipulator($file);

        $flacManipulator->converter = $this->converter;
        $flacManipulator->tagger = $this->tagger;

        return $flacManipulator;
    }
}
