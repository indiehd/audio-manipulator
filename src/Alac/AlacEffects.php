<?php

namespace IndieHD\AudioManipulator\Alac;

use IndieHD\AudioManipulator\Alac\AlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;
use IndieHD\AudioManipulator\CliCommand\FfmpegCommandInterface;

class AlacEffects implements AlacEffectInterface
{
    public function __construct(FfmpegCommandInterface $command)
    {
        $this->command = $command;
    }

    public function getCommand(): CliCommandInterface
    {
        return $this->command;
    }
}
