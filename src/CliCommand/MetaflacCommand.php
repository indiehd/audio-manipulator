<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\MetaflacCommandInterface;

class MetaflacCommand extends CliCommand implements MetaflacCommandInterface
{
    protected $name = 'metaflac_command';

    protected $binary = 'metaflac';

    protected $parts = [
        'options' => [],        // Global options
        'operations' => [],     // Operations to perform
        'flacfile-in' => [],   // Input file
        'flacfile-out' => [],  // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('METAFLAC_BINARY'))) {
            $this->binary = getenv('METAFLAC_BINARY');
        }
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('flacfile-in', escapeshellarg($inputFile));
    }

    public function output(string $outputFile): void
    {
        $this->addArgument('flacfile-out', escapeshellarg($outputFile));
    }

    public function removeAll(): void
    {
        $this->addArgument('operations', '--remove-all');
    }

    public function setTag(string $field, string $value): void
    {
        $this->addArgument('operations', '--set-tag=' . escapeshellarg($field)
            . '=' . escapeshellarg($value));
    }

    public function removeBlockType(array $types): void
    {
        $this->addArgument('operations', '--remove --block-type=' . implode(',', $types));
    }

    public function importPicture(string $fileOrSpecification): void
    {
        $this->addArgument('operations', '--import-picture-from=' . escapeshellarg($fileOrSpecification));
    }
}
