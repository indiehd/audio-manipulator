<?php

namespace IndieHD\AudioManipulator\Flac;

interface FlacManipulatorCreatorInterface
{
    public function create(string $file): FlacManipulator;
}
