<?php

namespace IndieHD\AudioManipulator;

use IndieHD\AudioManipulator\ManipulatorInterface;

class BaseManipulator implements ManipulatorInterface
{
    public function getFile(): string
    {
        return $this->file;
    }
}
