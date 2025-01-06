<?php

namespace IndieHD\AudioManipulator\Processing;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process implements ProcessInterface
{
    protected SymfonyProcess $process;
    protected array $command;
    protected float $timeout;
    protected string $locale = 'en_US.UTF-8';

    public function setProcess(array $command)
    {
        $this->process = SymfonyProcess::fromShellCommandline(join(' ', $command));
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

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function run(callable $callback = null, $env = [])
    {
        $this->process->setTimeout($this->timeout);

        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8
        // character used in command values/arguments will equate to an empty
        // string.

        setlocale(LC_CTYPE, $this->locale);

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
