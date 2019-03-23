<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process implements ProcessInterface
{
    protected $process;
    protected $timeout;

    public function setProcess($process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function run(string $command)
    {
        $this->process = new SymfonyProcess($command);

        $this->process->setTimeout($this->timeout);

        $this->process->run();
        
        return $this->process;
    }

    public function setTimeout(int $seconds)
    {
        $this->timeout = $seconds;
    }

    public function isSuccessful()
    {
        return $this->process->isSuccessful();
    }

    public function getOutput()
    {
       return $this->process->getOutput();
    }
}
