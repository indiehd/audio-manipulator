<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\Mid3v2CommandInterface;

class Mid3v2Command extends CliCommand implements Mid3v2CommandInterface
{
    protected $name = 'mid3v2_command';

    protected $binary = 'mid3v2';

    protected $parts = [
        'options' => [],    // Global options
        'infile' => [],     // Input file
    ];

    public function __construct()
    {
        if (!empty(getenv('MID3V2_BINARY'))) {
            $this->binary = getenv('MID3V2_BINARY');
        }
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('infile', escapeshellarg($inputFile));
    }

    public function quiet(): void
    {
        $this->addArgument('options', '--quiet');
    }

    public function song(string $value): void
    {
        $this->addArgument('options', '--song=' . escapeshellarg($value));
    }

    public function artist(string $value): void
    {
        $this->addArgument('options', '--artist=' . escapeshellarg($value));
    }

    public function year(string $value): void
    {
        $this->addArgument('options', '--year=' . escapeshellarg($value));
    }

    public function comment(string $value): void
    {
        $this->addArgument('options', '--comment=0:' . escapeshellarg($value));
    }

    public function album(string $value): void
    {
        $this->addArgument('options', '--album=' . escapeshellarg($value));
    }

    public function track(string $value): void
    {
        $this->addArgument('options', '--track=' . escapeshellarg($value));
    }

    public function genre(string $value): void
    {
        $this->addArgument('options', '--genre=' . escapeshellarg($value));
    }

    public function deleteAll()
    {
        $this->addArgument('options', '--delete-all');
    }

    public function picture(string $value): void
    {
        $this->addArgument('options', '--picture=' . escapeshellarg($value));
    }

    public function removeArtwork()
    {
        $this->addArgument('options', '--delete-frames=APIC');
    }

    public function removeTags(array $tags): void
    {
        $this->addArgument('options', '--delete-frames=' . escapeshellarg(implode(',', $tags)));
    }
}
