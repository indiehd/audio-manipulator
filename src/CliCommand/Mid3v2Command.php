<?php

namespace IndieHD\AudioManipulator\CliCommand;

class Mid3v2Command extends CliCommand implements Mid3v2CommandInterface
{
    protected string $name = 'mid3v2_command';

    protected string $binary = 'mid3v2';

    protected array $parts = [
        'options' => [],    // Global options
        'infile'  => [],     // Input file
    ];

    public function __construct()
    {
        if (!empty(getenv('MID3V2_BINARY'))) {
            $this->binary = getenv('MID3V2_BINARY');
        }
    }

    public function input(string $inputFile): Mid3v2Command
    {
        $this->addArgument('infile', escapeshellarg($inputFile));

        return $this;
    }

    public function quiet(): Mid3v2Command
    {
        $this->addArgument('options', '--quiet');

        return $this;
    }

    public function song(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--song='.escapeshellarg($value));

        return $this;
    }

    public function artist(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--artist='.escapeshellarg($value));

        return $this;
    }

    public function year(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--year='.escapeshellarg($value));

        return $this;
    }

    public function comment(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--comment=0:'.escapeshellarg($value));

        return $this;
    }

    public function album(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--album='.escapeshellarg($value));

        return $this;
    }

    public function track(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--track='.escapeshellarg($value));

        return $this;
    }

    public function genre(string $value): Mid3v2Command
    {
        $this->addArgument('options', '--genre='.escapeshellarg($value));

        return $this;
    }

    public function deleteAll(): Mid3v2Command
    {
        $this->addArgument('options', '--delete-all');

        return $this;
    }

    public function picture(string $imageFile, string $audioFile): Mid3v2Command
    {
        $this->addArgument('options', '--APIC '.escapeshellarg($imageFile).' '.escapeshellarg($audioFile));

        return $this;
    }

    public function removeArtwork(): Mid3v2Command
    {
        $this->addArgument('options', '--delete-frames=APIC');

        return $this;
    }

    public function removeTags(array $tags): Mid3v2Command
    {
        $this->addArgument('options', '--delete-frames='.escapeshellarg(implode(',', $tags)));

        return $this;
    }
}
