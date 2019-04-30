<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface LameCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): LameCommand;

    public function output(string $outputFile): LameCommand;

    public function quiet(): LameCommand;

    public function enableAndForceLameTag(): LameCommand;

    public function noReplayGain(): LameCommand;

    public function quality(int $quality): LameCommand;

    public function resample(float $frequency): LameCommand;

    public function bitwidth(int $width): LameCommand;

    public function cbr(): LameCommand;

    public function bitrate(int $rate): LameCommand;

    public function abr(): LameCommand;

    public function vbr(int $quality): LameCommand;

    public function setTitle(string $value): LameCommand;

    public function setArtist(string $value): LameCommand;

    public function setYear(string $value): LameCommand;

    public function setComment(string $value): LameCommand;

    public function setAlbum(string $value): LameCommand;

    public function setTracknumber(string $value): LameCommand;

    public function setGenre(string $value): LameCommand;
}
