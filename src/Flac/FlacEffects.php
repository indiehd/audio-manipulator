<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Flac\FlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

class FlacEffects implements FlacEffectInterface
{

    protected $commandParts;

    public function __construct(CliCommandInterface $command)
    {
        // TODO Discuss this comment and remove if not necessary, ultimately.

        // In EVERY command there is a name() method that returns the container references instance ?
        // $command = app()->builder->get($command->name());

        $this->command = $command;
    }

    public function getCommand(): CliCommandInterface
    {
        return $this->command;
    }
}
