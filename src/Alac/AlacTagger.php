<?php

namespace IndieHD\AudioManipulator\Alac;

use Psr\Log\LoggerInterface;
use Monolog\Handler\HandlerInterface;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\CliCommand\AtomicParsleyCommandInterface;

class AlacTagger implements TaggerInterface
{
    private $env;
    private $logName = 'ALAC_TAGGER_LOG';
    private $loggingEnabled = false;

    public $tagVerifier;
    private $process;
    private $logger;
    private $handler;
    public $command;

    public function __construct(
        TagVerifierInterface $tagVerifier,
        ProcessInterface $process,
        LoggerInterface $logger,
        HandlerInterface $handler,
        AtomicParsleyCommandInterface $command
    ) {
        $this->tagVerifier = $tagVerifier;
        $this->process = $process;
        $this->logger = $logger;
        $this->handler = $handler;
        $this->command = $command;

        $this->configureLogger();

        $this->env = ['LC_ALL' => 'en_US.utf8'];
    }

    protected function configureLogger(): void
    {
        if (!empty(getenv($this->logName))) {
            $this->logger->pushHandler($this->handler);
        }

        if (getenv('ENABLE_LOGGING') === 'true') {
            $this->loggingEnabled = true;
        }
    }

    protected function log(string $message, string $level = 'info'): void
    {
        if ($this->loggingEnabled) {
            $this->logger->{$level}($message);
        }
    }

    public function writeTags(string $file, array $tagData): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file "' . $file . '" appears not to exist');
        }

        $this->command
            ->input($file)
            ->overwrite();

        $this->attemptWrite($tagData);

        $fieldMappings = [
            'year' => 'creation_date',
            'tracknum' => 'track_number',
        ];

        $this->tagVerifier->verify($file, $tagData, $fieldMappings);
    }

    public function removeAllTags(string $file): void
    {
        $this->command
            ->input($file)
            ->overwrite()
            ->deleteAll();

        $this->runProcess($this->command->compose());
    }

    public function removeTags(string $file, array $tags): void
    {
        $this->command
            ->input($file)
            ->overwrite()
            ->removeTags($tags);

        $this->runProcess($this->command->compose());
    }

    public function writeArtwork(string $audioFile, string $imageFile): void
    {
        $this->command
            ->input($audioFile)
            ->overwrite()
            ->setArtwork($imageFile);

        $this->runProcess($this->command->compose());
    }

    public function removeArtwork(string $file): void
    {
        $this->command
            ->input($file)
            ->overwrite()
            ->removeArtwork();

        $this->runProcess($this->command->compose());
    }

    protected function runProcess(array $cmd): Process
    {
        // TODO Determine whether or not this is truly necessary, via tests,
        // i.e., when dealing with UTF-8 encoding.

        #$this->process->setLocale('en_US.UTF-8');

        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null, $this->env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->log(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
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
