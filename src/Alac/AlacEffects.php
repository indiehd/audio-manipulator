<?php

namespace IndieHD\AudioManipulator\Alac;

use IndieHD\AudioManipulator\Effects\Effects;
use IndieHD\AudioManipulator\Alac\AlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

class AlacEffects extends Effects implements AlacEffectInterface
{
    public function __construct(CliCommandInterface $command)
    {
        $this->command = $command;
    }
}
