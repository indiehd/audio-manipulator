<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process implements ProcessInterface
{
    public function run(array $command)
    {
        $p = new SymfonyProcess($command);
        
        $p->run();
        
        return $p;
    }
}
