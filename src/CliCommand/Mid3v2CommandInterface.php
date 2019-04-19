<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface Mid3v2CommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function quiet(): void;

    public function song(string $value): void;

    public function artist(string $value): void;

    public function year(string $value): void;

    public function comment(string $value): void;

    public function album(string $value): void;

    public function track(string $value): void;

    public function genre(string $value): void;
}
