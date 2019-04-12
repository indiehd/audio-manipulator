<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\FfmpegCommandInterface;

class FfmpegCommand extends CliCommand implements FfmpegCommandInterface
{
    protected $name = 'ffmpeg_command';

    protected $binary = 'ffmpeg';

    protected $parts = [
        'options' => [],            // Global options
        'infile-options' => [],     // File options (input)
        'infile' => [],             // Input file
        'outfile-options' => [],    // File options (output)
        'outfile' => [],            // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('FFMPEG_BINARY'))) {
            $this->binary = getenv('FFMPEG_BINARY');
        }
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('infile', '-i ' . escapeshellarg($inputFile));
    }

    public function output(string $outputFile): void
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));
    }

    public function overwriteOutput(): void
    {
        $this->addArgument('options', '-y');
    }

    public function forceAudioCodec(string $codec): void
    {
        $this->addArgument('outfile-options', '-acodec ' . $codec);
    }
}
