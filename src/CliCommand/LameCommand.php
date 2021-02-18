<?php

namespace IndieHD\AudioManipulator\CliCommand;

class LameCommand extends CliCommand implements LameCommandInterface
{
    protected $name = 'lame_command';

    protected $binary = 'lame';

    protected $parts = [
        'options' => [],    // Global options
        'infile'  => [],     // Input file
        'outfile' => [],    // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('LAME_BINARY'))) {
            $this->binary = getenv('LAME_BINARY');
        }
    }

    public function input(string $inputFile): LameCommand
    {
        $this->addArgument('infile', escapeshellarg($inputFile));

        return $this;
    }

    public function output(string $outputFile): LameCommand
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));

        return $this;
    }

    public function quiet(): LameCommand
    {
        $this->addArgument('options', '--quiet');

        return $this;
    }

    public function enableAndForceLameTag(): LameCommand
    {
        $this->addArgument('options', '-T');

        return $this;
    }

    public function noReplayGain(): LameCommand
    {
        $this->addArgument('options', '--noreplaygain');

        return $this;
    }

    public function quality(int $quality): LameCommand
    {
        $this->addArgument('options', '--q '.$quality);

        return $this;
    }

    public function resample(float $frequency): LameCommand
    {
        $this->addArgument('options', '--resample '.$frequency);

        return $this;
    }

    public function bitwidth(int $width): LameCommand
    {
        $this->addArgument('options', '--bitwidth '.$width);

        return $this;
    }

    public function cbr(): LameCommand
    {
        $this->addArgument('options', '--cbr');

        return $this;
    }

    public function bitrate(int $rate): LameCommand
    {
        $this->addArgument('options', '-b '.$rate);

        return $this;
    }

    public function abr(): LameCommand
    {
        $this->addArgument('options', '--abr');

        return $this;
    }

    public function vbr(int $quality): LameCommand
    {
        $this->addArgument('options', '--vbr-new -V '.$quality);

        return $this;
    }

    public function setTitle(string $value): LameCommand
    {
        $this->addArgument('options', '--tt '.escapeshellarg($value));

        return $this;
    }

    public function setArtist(string $value): LameCommand
    {
        $this->addArgument('options', '--ta '.escapeshellarg($value));

        return $this;
    }

    public function setYear(string $value): LameCommand
    {
        $this->addArgument('options', '--ty '.escapeshellarg($value));

        return $this;
    }

    public function setComment(string $value): LameCommand
    {
        $this->addArgument('options', '--tc '.escapeshellarg($value));

        return $this;
    }

    public function setAlbum(string $value): LameCommand
    {
        $this->addArgument('options', '--tl '.escapeshellarg($value));

        return $this;
    }

    public function setTracknumber(string $value): LameCommand
    {
        $this->addArgument('options', '--tn '.escapeshellarg($value));

        return $this;
    }

    public function setGenre(string $value): LameCommand
    {
        $this->addArgument('options', '--tg '.escapeshellarg($value));

        return $this;
    }
}
