<?php

namespace IndieHD\AudioManipulator\CliCommand;

abstract class CliCommand implements CliCommandInterface
{
    // TODO Discuss this commented code and remove if not necessary, ultimately.

    //protected $instance;

    //public function instance()
    //{
    //    return $this->instance;
    //}

    protected $name;

    protected $binary;

    protected $parts;

    public function setBinary(string $binary)
    {
        $this->binary = $binary;
    }

    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getCommandParts(): array
    {
        return $this->parts;
    }

    public function addArgument(string $name, string $value): void
    {
        if (!array_key_exists($name, $this->parts)) {
            throw new \InvalidArgumentException(
                'The "'.$this->binary.'" command does not contain a part named "'.$name.'"'
            );
        }

        array_push($this->parts[$name], $value);
    }

    public function removeAllArguments(): void
    {
        foreach ($this->parts as $name => $value) {
            $this->parts[$name] = [];
        }
    }

    public function compose(): array
    {
        $command = [$this->binary];

        foreach ($this->parts as $values) {
            foreach ($values as $value) {
                $command[] = $value;
            }
        }

        return $command;
    }

    public function asString(): string
    {
        return implode(' ', $this->compose());
    }
}
