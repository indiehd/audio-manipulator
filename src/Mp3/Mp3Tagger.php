<?php

namespace IndieHD\AudioManipulator\Mp3;

use getID3;
use getid3_writetags;

use Psr\Log\LoggerInterface;
use Monolog\Handler\HandlerInterface;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\FilenameSanitizer\FilenameSanitizerInterface;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\CliCommand\Mid3v2CommandInterface;

class Mp3Tagger implements TaggerInterface
{
    private $logName = 'MP3_TAGGER_LOG';
    private $loggingEnabled = false;

    public function __construct(
        getID3 $getid3,
        getid3_writetags $writeTags,
        ProcessInterface $process,
        LoggerInterface $logger,
        HandlerInterface $handler,
        FilenameSanitizerInterface $filenameSanitizer,
        Mid3v2CommandInterface $command,
        ValidatorInterface $validator
    ) {
        $this->getid3 = $getid3;
        $this->writeTags= $writeTags;
        $this->process = $process;
        $this->logger = $logger;
        $this->handler = $handler;
        $this->filenameSanitizer = $filenameSanitizer;
        $this->command = $command;
        $this->validator = $validator;

        $this->configureLogger();

        // This option is specific to the tag READER (the WRITER has its own,
        // separate encoding setting).

        $this->getid3->setOption(['encoding' => 'UTF-8']);

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

        $this->command->input($file);

        $this->attemptWrite($tagData);

        $fieldMappings = [
            'song' => 'title',
            'track' => 'track_number',
        ];

        $this->verifyTagData($file, $tagData, $fieldMappings);
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

    // TODO As it stands, this function is problematic because the Vorbis Comment
    // standard allows for multiple instances of the same tag name, e.g., passing
    // --set-tag=ARTIST=Foo --set-tag=ARTIST=Bar is perfectly valid. This function
    // should be modified to accommodate that fact.

    protected function verifyTagData(string $file, array $tagData, array $fieldMappings = null): void
    {
        $fileDetails = $this->getid3->analyze($file);

        $tagsOnFile = $fileDetails['tags']['id3v2'];

        $failures = [];

        // Compare the passed tag data to the values acquired from the file.

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                if (isset($fieldMappings[$fieldName])) {
                    $fieldName = $fieldMappings[$fieldName];
                }

                if ($tagsOnFile[$fieldName][0] != $fieldValue) {
                    $failures[] = $fieldName . ' (' . $tagsOnFile[$fieldName][0]. ' != ' . $fieldValue . ')';
                }
            }
        }

        if (count($failures) > 0) {
            throw new AudioTaggerException(
                'Expected value does not match actual value for tags: ' . implode(', ', $failures)
            );
        }
    }
}
