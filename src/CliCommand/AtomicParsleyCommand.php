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
        if (!empty(getenv('LAME_BINARY'))) {
            $this->binary = getenv('LAME_BINARY');
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
}
