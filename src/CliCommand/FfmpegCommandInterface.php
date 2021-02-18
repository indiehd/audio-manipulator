<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface FfmpegCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): FfmpegCommand;

    public function output(string $outputFile): FfmpegCommand;

    public function overwriteOutput(): FfmpegCommand;

    public function forceAudioCodec(string $codec): FfmpegCommand;

    public function forceVideoCodec(string $codec): FfmpegCommand;

    public function disableVideo(): FfmpegCommand;
}
