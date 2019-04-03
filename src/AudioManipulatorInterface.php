<?php

namespace IndieHD\AudioManipulator;

interface AudioManipulatorInterface
{
    public function convert(string $output);

    public function writeTags(array $data);

    public function removeTags(array $data);
}
