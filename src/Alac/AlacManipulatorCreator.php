<?php

namespace IndieHD\AudioManipulator\Alac;

use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class AlacManipulatorCreator implements AlacManipulatorCreatorInterface
{
    public TaggerInterface $tagger;

    public function __construct(
        TaggerInterface $tagger
    ) {
        $this->tagger = $tagger;
    }

    public function create(string $file): AlacManipulator
    {
        $manipulator = new AlacManipulator($file);

        $manipulator->tagger = $this->tagger;

        return $manipulator;
    }
}
