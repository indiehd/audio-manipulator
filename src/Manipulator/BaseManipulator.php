<?php

namespace IndieHD\AudioManipulator\Manipulator;

use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class BaseManipulator implements ManipulatorInterface
{
    protected string $file;

    public ConverterInterface $converter;

    public TaggerInterface $tagger;

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
