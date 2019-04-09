<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\SoxCommandInterface;

class SoxCommand extends CliCommand implements SoxCommandInterface
{
    protected $name = 'sox_command';

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

    public function getName(): string
    {
        return $this->name;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getCommandParts(): array
    {
        return $this->parts;
    }

    public function addArgument(string $name, string $value): void
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
        $this->addArgument('gopts', '--single-threaded');
    }

    public function verbosity(int $level): void
    {
        $this->addArgument('gopts', '-V' . (string) $level);
    }

    public function input(string $inputFile): void
    {
        $this->addArgument('infile', escapeshellarg($inputFile));
    }

    public function channels(int $channels): void
    {
        $this->addArgument('fopts-in', '--channels ' . $channels);
    }

    public function output(string $outputFile): void
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));
    }

    public function fade(
        string $type = null,
        float $fadeInLength,
        float $stopPosition = null,
        float $fadeOutLength = null
    ): void {
        $effect = ['fade'];

        if (!is_null($type)) {
            $effect[] = $type;
        }

        $effect[] = $fadeInLength;

        if (!is_null($stopPosition)) {
            $effect[] = $stopPosition;

            if (!is_null($fadeOutLength)) {
                $effect[] = $fadeOutLength;
            }
        }

        $this->addArgument('effopt', implode(' ', $effect));
    }
}
