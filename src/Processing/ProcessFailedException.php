<?php

namespace IndieHD\AudioManipulator\Processing;

use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

use IndieHD\AudioManipulator\Processing\ProcessInterface;

class ProcessFailedException extends SymfonyProcessFailedException
{
    public function __construct(ProcessInterface $process)
    {
        throw new SymfonyProcessFailedException($process->getProcess());
    }
}
