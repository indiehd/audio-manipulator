<?php

namespace IndieHD\AudioManipulator\Manipulator;

class BaseManipulator implements ManipulatorInterface
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
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
