<?php

namespace IndieHD\AudioManipulator\Tagging;

interface TaggerManipulatorInterface
{
    public function writeTags(array $data);

    public function removeTags(array $data);
}
