<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Effects\Effects;
use IndieHD\AudioManipulator\Flac\FlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

class FlacEffects extends Effects implements FlacEffectInterface
{
    public function __construct(CliCommandInterface $command)
    {
        $this->command = $command;
    }
}
