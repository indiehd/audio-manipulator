<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\SoxCommandInterface;

class SoxCommand extends CliCommand implements SoxCommandInterface
{
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
        return 'sox_command';
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
}
