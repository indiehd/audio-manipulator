<?php

namespace IndieHD\AudioManipulator\CliCommand;

interface CliCommandInterface
{
    public function name(): string;

    public function addPart(string $name, string $value): void;

    public function compose(): array;
}
