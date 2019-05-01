<?php

namespace IndieHD\AudioManipulator\Alac;

interface AlacManipulatorCreatorInterface
{
    public function create(string $file): AlacManipulator;
}
