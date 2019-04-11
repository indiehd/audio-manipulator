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
}
