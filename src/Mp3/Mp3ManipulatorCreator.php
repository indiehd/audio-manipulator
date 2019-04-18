<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class Mp3ManipulatorCreator implements Mp3ManipulatorCreatorInterface
{
    public function __construct(
        TaggerInterface $tagger
    ) {
        $this->tagger = $tagger;
    }

    public function create(string $file): Mp3Manipulator
    {
        $manipulator = new Mp3Manipulator($file);

        $manipulator->tagger = $this->tagger;

        return $manipulator;
    }
}
