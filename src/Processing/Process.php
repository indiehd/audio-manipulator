<?php

namespace IndieHD\AudioManipulator\Processing;

use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

use IndieHD\AudioManipulator\Processing\ProcessInterface;

class Process implements ProcessInterface
{
    protected $process;
    protected $command;
    protected $timeout;

    public function setProcess(array $command)
    {
        $this->process = new SymfonyProcess(join(' ', $command));
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function setCommand(array $command)
    {
        $this->command = $command;

        $this->setProcess($command);
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function run(callable $callback = null, $env = [])
    {
        $this->process->setTimeout($this->timeout);

        $this->process->run($callback, $env);

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

    public function getErrorOutput()
    {
        return $this->process->getErrorOutput();
    }
}
