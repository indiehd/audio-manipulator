<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

use IndieHD\AudioManipulator\ProcessInterface;

class ProcessFailedException extends SymfonyProcessFailedException
{
    public function __construct(ProcessInterface $process)
    {
        throw new SymfonyProcessFailedException($process->getProcess());
    }
}
