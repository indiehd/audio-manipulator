<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\CliCommand\MetaflacCommandInterface;
use IndieHD\AudioManipulator\Logging\LoggerInterface;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FlacTagger implements TaggerInterface
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
        MetaflacCommandInterface $command
    ) {
        $this->tagVerifier = $tagVerifier;
        $this->process = $process;
        $this->logger = $logger;
        $this->command = $command;

        $this->logger->configureLogger('FLAC_TAGGER_LOG');

        // If "['LC_ALL' => 'en_US.UTF-8']" is not passed here, any UTF-8
        // character will appear as a "#" symbol in the resultant tag value.

        $this->env = ['LC_ALL' => 'en_US.UTF-8'];
    }

    /**
     * Add metadata tags to FLAC files.
     *
     * @param string $file
     * @param array  $tagData
     *
     * @return array
     */
    public function writeTags(string $file, array $tagData): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file "'.$file.'" appears not to exist');
        }

        $this->command->input($file);

        // TODO Removing all tags as a matter of course is problematic because
        // the Artist may have added custom tags that he/she spent considerable
        // time creating, as in the case of normalization data. It should be
        // determined whether or not this is still necessary.

        // Attempt to remove any existing tags before writing new tags.
        // IMPORTANT: The --remove-vc-all option is deprecated in favor of the
        // --remove-all-tags option; using the deprecated option will cause the
        // command to fail on systems on which the option is not supported.
        // Changed to --remove-all because cover art was not being removed.
        // -CBJ 2011.01.18

        $this->removeAllTags($file);

        $this->command->input($file);

        $this->attemptWrite($tagData);

        $this->tagVerifier->verify($file, $tagData);
    }

    public function removeAllTags(string $file): void
    {
        $this->command
            ->input($file)
            ->removeAll();

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
        $this->command
            ->input($audioFile)
            ->importPicture($imageFile);

        $this->runProcess($this->command->compose());
    }

    public function removeArtwork(string $file): void
    {
        $this->command
            ->input($file)
            ->removeBlockType(['PICTURE']);

        $this->runProcess($this->command->compose());
    }

    protected function runProcess(array $cmd): Process
    {
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
                $this->command->setTag($fieldName, $fieldValue);
            }
        }

        $this->runProcess($this->command->compose());
    }
}
