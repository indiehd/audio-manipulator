<?php

namespace IndieHD\AudioManipulator\CliCommand;

abstract class CliCommand implements CliCommandInterface
{
    protected $instance;

    public function instance()
    {
        return $this->instance;
    }

    abstract public function name();

    abstract public function addPart(string $name, string $value);
}