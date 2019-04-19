<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\BaseManipulator;
use IndieHD\AudioManipulator\ManipulatorInterface;
use IndieHD\AudioManipulator\Tagging\TaggerManipulatorInterface;

class Mp3Manipulator extends BaseManipulator implements ManipulatorInterface, TaggerManipulatorInterface
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function writeTags(array $data)
    {
        $this->tagger->writeTags($this->file, $data);
    }

    public function removeTags(array $data)
    {
        $this->tagger->removeTags($this->file, $data);
    }
}
