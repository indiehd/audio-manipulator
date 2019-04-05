<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\SoxCommandInterface;

class SoxCommand extends CliCommand implements SoxCommandInterface
{
    private $name = 'sox_command';

    protected $binary = 'sox';

    protected $parts = [
        'gopts' => [],      // Global options
        'fopts-in' => [],   // File options (input)
        'infile' => [],     // Input file
        'fopts-out' => [],  // File options (output)
        'outfile' => [],    // Output file
        'effect' => [],     // Effect
        'effopt' => [],     // Effect options
    ];

    public function name(): string
    {
        return $this->name;
    }

    public function addPart(string $name, string $value): void
    {
        if (!array_key_exists($name, $this->parts)) {
            throw new \InvalidArgumentException(
                'The "' . $this->binary . '" command does not contain a part named "' . $name . '"'
            );
        }

        array_push($this->parts[$name], $value);
    }

    public function compose(): array
    {
        $command = [$this->binary];

        foreach ($this->parts as $values) {
            foreach ($values as $value) {
                $command[] = $value;
            }
        }

        return $command;
    }

    public function singleThreaded(): void
    {
        $this->addPart('gopts', '--single-threaded');
    }

    public function verbosity(int $level): void
    {
        $this->addPart('gopts', '-V' . (string) $level);
    }

    public function input(string $inputFile): void
    {
        $this->addPart('infile', escapeshellarg($inputFile));
    }

    public function channels(int $channels): void
    {
        $this->addPart('fopts-in', '--channels ' . $channels);
    }

    public function output(string $outputFile): void
    {
        $this->addPart('outfile', escapeshellarg($outputFile));
    }
}
