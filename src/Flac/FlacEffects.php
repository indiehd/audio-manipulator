<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\CliCommand\SoxCommandInterface;
use IndieHD\AudioManipulator\Flac\FlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

class FlacEffects implements FlacEffectInterface
{
    public function __construct(SoxCommandInterface $command)
    {
        $this->command = $command;
    }

    public function getCommand(): CliCommandInterface
    {
        return $this->command;
    }
}
