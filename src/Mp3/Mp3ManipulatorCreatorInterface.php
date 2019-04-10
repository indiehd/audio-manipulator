<?php

namespace IndieHD\AudioManipulator\Mp3;

interface Mp3ManipulatorCreatorInterface
{
    public function create(string $file): Mp3Manipulator;
}
