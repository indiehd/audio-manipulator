<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface LameCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): void;

    public function output(string $outputFile): void;

    public function quiet(): void;

    public function enableAndForceLameTag(): void;

    public function noReplayGain(): void;

    public function quality(int $quality): void;

    public function resample(float $frequency): void;

    public function bitwidth(int $width): void;

    public function cbr(): void;

    public function bitrate(int $rate): void;

    public function abr(): void;

    public function vbr(int $quality): void;
}
