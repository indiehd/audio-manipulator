<?php

namespace IndieHD\AudioManipulator\CliCommand;

class FfmpegCommand extends CliCommand implements FfmpegCommandInterface
{
    protected string $name = 'ffmpeg_command';

    protected string $binary = 'ffmpeg';

    protected array $parts = [
        'options'         => [],            // Global options
        'infile-options'  => [],     // File options (input)
        'infile'          => [],             // Input file
        'outfile-options' => [],    // File options (output)
        'outfile'         => [],            // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('FFMPEG_BINARY'))) {
            $this->binary = getenv('FFMPEG_BINARY');
        }
    }

    public function input(string $inputFile): FfmpegCommand
    {
        $this->addArgument('infile', '-i '.escapeshellarg($inputFile));

        return $this;
    }

    public function output(string $outputFile): FfmpegCommand
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));

        return $this;
    }

    public function overwriteOutput(): FfmpegCommand
    {
        $this->addArgument('options', '-y');

        return $this;
    }

    public function forceAudioCodec(string $codec): FfmpegCommand
    {
        $this->addArgument('outfile-options', '-acodec '.$codec);

        return $this;
    }

    public function forceVideoCodec(string $codec): FfmpegCommand
    {
        $this->addArgument('outfile-options', '-vcodec '.$codec);

        return $this;
    }

    public function disableVideo(): FfmpegCommand
    {
        $this->addArgument('outfile-options', '-vn');

        return $this;
    }
}
