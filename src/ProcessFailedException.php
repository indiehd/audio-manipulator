<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

class ProcessFailedException extends SymfonyProcessFailedException
{
    public function __construct(Process $process)
    {
        throw new SymfonyProcessFailedException($process->getProcess());
    }
}
