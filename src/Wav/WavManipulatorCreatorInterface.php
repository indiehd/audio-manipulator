<?php

namespace IndieHD\AudioManipulator\Wav;

interface WavManipulatorCreatorInterface
{
    public function create(string $file): WavManipulator;
}
