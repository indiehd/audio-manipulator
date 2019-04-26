<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\AtomicParsleyCommandInterface;

class AtomicParsleyCommand extends CliCommand implements AtomicParsleyCommandInterface
{
    protected $name = 'atomic_parsley_command';

    protected $binary = 'AtomicParsley';

    protected $parts = [
        'infile' => [],     // Input file
        'options' => [],    // Global options
    ];

    public function __construct()
    {
        if (!empty(getenv('ATOMIC_PARSLEY_BINARY'))) {
            $this->binary = getenv('ATOMIC_PARSLEY_BINARY');
        }
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('infile', escapeshellarg($inputFile));
    }

    public function setArtwork(string $imageFile): void
    {
        $this->addArgument('options', '--artwork ' . escapeshellarg($imageFile));
    }

    public function overwrite(): void
    {
        $this->addArgument('options', '--overWrite');
    }

    public function deleteAll(): void
    {
        $this->addArgument('options', '--metaEnema');
    }

    public function removeArtwork(): void
    {
        $this->addArgument('options', '--artwork REMOVE_ALL');
    }

    public function title(string $value): void
    {
        $this->addArgument('options', '--title ' . escapeshellarg($value));
    }

    public function artist(string $value): void
    {
        $this->addArgument('options', '--artist ' . escapeshellarg($value));
    }

    public function year(string $value): void
    {
        $this->addArgument('options', '--year ' . escapeshellarg($value));
    }

    public function comment(string $value): void
    {
        $this->addArgument('options', '--comment ' . escapeshellarg($value));
    }

    public function album(string $value): void
    {
        $this->addArgument('options', '--album ' . escapeshellarg($value));
    }

    public function tracknum(string $value): void
    {
        $this->addArgument('options', '--tracknum ' . escapeshellarg($value));
    }

    public function genre(string $value): void
    {
        $this->addArgument('options', '--genre ' . escapeshellarg($value));
    }

    public function removeTags(array $tags): void
    {
        foreach ($tags as $name) {
            $this->addArgument('options', '--' . $name . '=' . escapeshellarg(''));
        }
    }
}
