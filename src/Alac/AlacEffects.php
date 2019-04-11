<?php

namespace IndieHD\AudioManipulator\Alac;

use IndieHD\AudioManipulator\Alac\AlacEffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

class AlacEffects implements AlacEffectInterface
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
