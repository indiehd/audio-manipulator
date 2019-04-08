<?php

namespace IndieHD\AudioManipulator\CliCommand;

interface CliCommandInterface
{
    public function name(): string;

    public function addArgument(string $name, string $value): void;

    public function compose(): array;
}
