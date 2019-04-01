<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\BaseManipulator;
use IndieHD\AudioManipulator\Converting\ConverterManipulatorInterface;
use IndieHD\AudioManipulator\Tagging\TaggerManipulatorInterface;

class Mp3Manipulator extends BaseManipulator implements ConverterManipulatorInterface, TaggerManipulatorInterface
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function convert(string $output, $filters = [])
    {
        $this->converter->applyFilters($filters)->convert($output);
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
