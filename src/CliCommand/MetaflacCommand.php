<?php

namespace IndieHD\AudioManipulator\CliCommand;

class MetaflacCommand extends CliCommand implements MetaflacCommandInterface
{
    protected $name = 'metaflac_command';

    protected $binary = 'metaflac';

    protected $parts = [
        'options'      => [],        // Global options
        'operations'   => [],     // Operations to perform
        'flacfile-in'  => [],   // Input file
        'flacfile-out' => [],  // Output file
    ];

    public function __construct()
    {
        if (!empty(getenv('METAFLAC_BINARY'))) {
            $this->binary = getenv('METAFLAC_BINARY');
        }
    }

    public function input(string $inputFile): MetaflacCommand
    {
        $this->addArgument('flacfile-in', escapeshellarg($inputFile));

        return $this;
    }

    public function output(string $outputFile): MetaflacCommand
    {
        $this->addArgument('flacfile-out', escapeshellarg($outputFile));

        return $this;
    }

    public function removeAll(): MetaflacCommand
    {
        $this->addArgument('operations', '--remove-all');

        return $this;
    }

    public function removeTags(array $tags): MetaflacCommand
    {
        foreach ($tags as $name) {
            $this->addArgument('operations', '--remove-tag='.escapeshellarg($name));
        }

        return $this;
    }

    public function setTag(string $field, string $value): MetaflacCommand
    {
        $this->addArgument('operations', '--set-tag='.escapeshellarg($field)
            .'='.escapeshellarg($value));

        return $this;
    }

    public function removeBlockType(array $types): MetaflacCommand
    {
        $this->addArgument('operations', '--remove --block-type='.implode(',', $types));

        return $this;
    }

    public function importPicture(string $fileOrSpecification): MetaflacCommand
    {
        $this->addArgument('operations', '--import-picture-from='.escapeshellarg($fileOrSpecification));

        return $this;
    }
}
