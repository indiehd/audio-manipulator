<?php

namespace IndieHD\AudioManipulator\CliCommand;

interface CliCommandInterface
{
    public function getName(): string;

    public function getBinary(): string;

    public function getCommandParts(): array;

    public function addArgument(string $name, string $value): void;

    public function compose(): array;
}
