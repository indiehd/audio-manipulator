<?php

namespace IndieHD\AudioManipulator\CliCommand;

abstract class CliCommand implements CliCommandInterface
{
    // TODO Discuss this commented code and remove if not necessary, ultimately.

    #protected $instance;

    #public function instance()
    #{
    #    return $this->instance;
    #}

    protected $name;

    protected $binary;

    protected $parts;

    abstract public function name(): string;

    abstract public function addArgument(string $name, string $value): void;
}
