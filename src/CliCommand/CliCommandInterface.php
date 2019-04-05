<?php

namespace IndieHD\AudioManipulator\CliCommand;

interface CliCommandInterface
{
    public function name();

    public function addPart(string $name, string $value);
}
