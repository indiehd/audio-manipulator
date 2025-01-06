<?php

namespace IndieHD\AudioManipulator\Wav;

use IndieHD\AudioManipulator\Converting\ConverterInterface;

class WavManipulatorCreator implements WavManipulatorCreatorInterface
{
    public ConverterInterface $converter;

    public function __construct(
        ConverterInterface $converter
    ) {
        $this->converter = $converter;
    }

    public function create(string $file): WavManipulator
    {
        $manipulator = new WavManipulator($file);

        $manipulator->converter = $this->converter;

        return $manipulator;
    }
}
