<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class Mp3ManipulatorCreator implements Mp3ManipulatorCreatorInterface
{
    public function __construct(
        ConverterInterface $converter,
        TaggerInterface $tagger
    ) {
        $this->converter = $converter;
        $this->tagger = $tagger;
    }

    public function create(string $file): Mp3Manipulator
    {
        $manipulator = new Mp3Manipulator($file);

        $manipulator->converter = $this->converter;
        $manipulator->tagger = $this->tagger;

        return $manipulator;
    }
}
