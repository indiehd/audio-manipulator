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

    public function input(string $inputFile): AtomicParsleyCommand
    {
        $this->addArgument('infile', escapeshellarg($inputFile));

        return $this;
    }

    public function setArtwork(string $imageFile): AtomicParsleyCommand
    {
        $this->addArgument('options', '--artwork ' . escapeshellarg($imageFile));

        return $this;
    }

    public function overwrite(): AtomicParsleyCommand
    {
        $this->addArgument('options', '--overWrite');

        return $this;
    }

    public function deleteAll(): AtomicParsleyCommand
    {
        $this->addArgument('options', '--metaEnema');

        return $this;
    }

    public function removeArtwork(): AtomicParsleyCommand
    {
        $this->addArgument('options', '--artwork REMOVE_ALL');

        return $this;
    }

    public function title(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--title ' . escapeshellarg($value));

        return $this;
    }

    public function artist(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--artist ' . escapeshellarg($value));

        return $this;
    }

    public function year(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--year ' . escapeshellarg($value));

        return $this;
    }

    public function comment(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--comment ' . escapeshellarg($value));

        return $this;
    }

    public function album(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--album ' . escapeshellarg($value));

        return $this;
    }

    public function tracknum(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--tracknum ' . escapeshellarg($value));

        return $this;
    }

    public function genre(string $value): AtomicParsleyCommand
    {
        $this->addArgument('options', '--genre ' . escapeshellarg($value));

        return $this;
    }

    public function removeTags(array $tags): AtomicParsleyCommand
    {
        foreach ($tags as $name) {
            $this->addArgument('options', '--' . $name . '=' . escapeshellarg(''));
        }

        return $this;
    }
}
