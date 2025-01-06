<?php

namespace IndieHD\AudioManipulator\Wav;

use IndieHD\AudioManipulator\Manipulator\BaseManipulator;
use IndieHD\AudioManipulator\Manipulator\ManipulatorInterface;

class WavManipulator extends BaseManipulator implements ManipulatorInterface
{
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
    }
}
