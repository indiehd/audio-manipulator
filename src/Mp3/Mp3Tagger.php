<?php

namespace IndieHD\AudioManipulator\Mp3;

use IndieHD\AudioManipulator\CliCommand\Mid3v2CommandInterface;
use IndieHD\AudioManipulator\Logging\LoggerInterface;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Mp3Tagger implements TaggerInterface
{
    private $env;
    public $tagVerifier;
    private $process;
    private $logger;
    public $command;

    public function __construct(
        TagVerifierInterface $tagVerifier,
        ProcessInterface $process,
        LoggerInterface $logger,
        Mid3v2CommandInterface $command
    ) {
        $this->tagVerifier = $tagVerifier;
        $this->process = $process;
        $this->logger = $logger;
        $this->command = $command;

        $this->logger->configureLogger('MP3_TAGGER_LOG');

        $this->env = ['LC_ALL' => 'en_US.utf8'];
    }

    public function writeTags(string $file, array $tagData): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file "'.$file.'" appears not to exist');
        }

        $this->command->input($file);

        $this->attemptWrite($tagData);

        $fieldMappings = [
            'song'  => 'title',
            'track' => [
                'name'    => 'track_number',
                'mutator' => function ($input) {
                    return explode('/', $input[0])[0];
                },
            ],
        ];

        $this->tagVerifier->verify($file, $tagData, $fieldMappings);
    }

    public function removeAllTags(string $file): void
    {
        $this->command
            ->input($file)
            ->deleteAll();

        $this->runProcess($this->command->compose());
    }

    public function removeTags(string $file, array $tags): void
    {
        $this->command
            ->input($file)
            ->removeTags($tags);

        $this->runProcess($this->command->compose());
    }

    public function writeArtwork(string $audioFile, string $imageFile): void
    {
        $this->command->picture($imageFile, $audioFile);

        $this->runProcess($this->command->compose());
    }

    public function removeArtwork(string $file): void
    {
        $this->command
            ->input($file)
            ->removeArtwork();

        $this->runProcess($this->command->compose());
    }

    protected function runProcess(array $cmd): Process
    {
        // TODO Determine whether or not this is truly necessary, via tests,
        // i.e., when dealing with UTF-8 encoding.

        //$this->process->setLocale('en_US.UTF-8');

        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null, $this->env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->log(
            $this->process->getProcess()->getCommandLine().PHP_EOL.PHP_EOL
            .$this->process->getOutput()
        );

        $this->command->removeAllArguments();

        return $this->process;
    }

    protected function attemptWrite(array $tagData): void
    {
        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                $this->command->{$fieldName}($fieldValue);
            }
        }

        $this->runProcess($this->command->compose());
    }
}
