<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\LameCommandInterface;

class LameCommand extends CliCommand implements LameCommandInterface
{
    protected $name = 'lame_command';

    protected $binary = 'lame';

    protected $parts = [
        'options' => [],    // Global options
        'infile' => [],     // Input file
        'outfile' => [],    // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('LAME_BINARY'))) {
            $this->binary = getenv('LAME_BINARY');
        }
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('infile', escapeshellarg($inputFile));
    }

    public function output(string $outputFile): void
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));
    }

    public function quiet(): void
    {
        $this->addArgument('options', '--quiet');
    }

    public function enableAndForceLameTag(): void
    {
        $this->addArgument('options', '-T');
    }

    public function noReplayGain(): void
    {
        $this->addArgument('options', '--noreplaygain');
    }

    public function quality(int $quality): void
    {
        $this->addArgument('options', '--q ' . $quality);
    }

    public function resample(float $frequency): void
    {
        $this->addArgument('options', '--resample ' . $frequency);
    }

    public function bitwidth(int $width): void
    {
        $this->addArgument('options', '--bitwidth ' . $width);
    }

    public function cbr(): void
    {
        $this->addArgument('options', '--cbr');
    }

    public function bitrate(int $rate): void
    {
        $this->addArgument('options', '-b ' . $rate);
    }

    public function abr(): void
    {
        $this->addArgument('options', '--abr');
    }

    public function vbr(int $quality): void
    {
        $this->addArgument('options', '--vbr-new -V ' . $quality);
    }

    public function setTitle(string $value): void
    {
        $this->addArgument('options', '--tt ' . escapeshellarg($value));
    }

    public function setArtist(string $value): void
    {
        $this->addArgument('options', '--ta ' . escapeshellarg($value));
    }

    public function setYear(string $value): void
    {
        $this->addArgument('options', '--ty ' . escapeshellarg($value));
    }

    public function setComment(string $value): void
    {
        $this->addArgument('options', '--tc ' . escapeshellarg($value));
    }

    public function setAlbum(string $value): void
    {
        $this->addArgument('options', '--tl ' . escapeshellarg($value));
    }

    public function setTracknumber(string $value): void
    {
        $this->addArgument('options', '--tn ' . escapeshellarg($value));
    }

    public function setGenre(string $value): void
    {
        $this->addArgument('options', '--tg ' . escapeshellarg($value));
    }
}
