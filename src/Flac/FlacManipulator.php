<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\BaseManipulator;
use IndieHD\AudioManipulator\ManipulatorInterface;
use IndieHD\AudioManipulator\Tagging\TaggerManipulatorInterface;

class FlacManipulator extends BaseManipulator implements TaggerManipulatorInterface
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function writeTags(array $data)
    {
        $this->tagger->writeTags($data);
    }

    public function removeTags(array $data)
    {
        $this->tagger->removeTags($data);
    }
}
