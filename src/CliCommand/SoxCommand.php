<?php

namespace IndieHD\AudioManipulator\CliCommand;

class SoxCommand extends CliCommand implements SoxCommandInterface
{
    protected string $name = 'sox_command';

    protected string $binary = 'sox';

    protected array $parts = [
        'gopts'     => [],      // Global options
        'fopts-in'  => [],   // File options (input)
        'infile'    => [],     // Input file
        'fopts-out' => [],  // File options (output)
        'outfile'   => [],    // Output file
        'effect'    => [],     // Effect
        'effopt'    => [],     // Effect options
    ];

    public function __construct()
    {
        if (!empty(getenv('SOX_BINARY'))) {
            $this->binary = getenv('SOX_BINARY');
        }
    }

    public function singleThreaded(): SoxCommand
    {
        $this->addArgument('gopts', '--single-threaded');

        return $this;
    }

    public function verbosity(int $level): SoxCommand
    {
        $this->addArgument('gopts', '-V'.(string) $level);

        return $this;
    }

    public function input(string $inputFile): SoxCommand
    {
        $this->addArgument('infile', escapeshellarg($inputFile));

        return $this;
    }

    public function channels(int $channels): SoxCommand
    {
        $this->addArgument('fopts-in', '--channels '.$channels);

        return $this;
    }

    public function output(string $outputFile): SoxCommand
    {
        $this->addArgument('outfile', escapeshellarg($outputFile));

        return $this;
    }

    public function fade(
        string $type = null,
        float $fadeInLength,
        float $stopPosition = null,
        float $fadeOutLength = null
    ): SoxCommand {
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

        return $this;
    }
}
