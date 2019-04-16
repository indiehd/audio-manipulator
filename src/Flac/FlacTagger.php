<?php

namespace IndieHD\AudioManipulator\Flac;

use getID3;
use getid3_writetags;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\FilenameSanitizer\FilenameSanitizerInterface;

use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\AudioManipulator\Validation\AudioValidatorException;

use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;

use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\CliCommand\MetaflacCommandInterface;

class FlacTagger implements TaggerInterface
{
    private $env;

    public function __construct(
        getID3 $getid3,
        getid3_writetags $writeTags,
        ProcessInterface $process,
        LoggerInterface $logger,
        FilenameSanitizerInterface $filenameSanitizer,
        MetaflacCommandInterface $command,
        ValidatorInterface $validator
    ) {
        $this->getid3 = $getid3;
        $this->writeTags= $writeTags;
        $this->process = $process;
        $this->logger = $logger;
        $this->filenameSanitizer = $filenameSanitizer;
        $this->command = $command;
        $this->validator = $validator;

        // TODO Make the log location configurable.

        $fileHandler = new StreamHandler(
            'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR
            . 'flac-tagger.log',
            Logger::INFO
        );

        $this->logger->pushHandler($fileHandler);

        $this->env = ['LC_ALL' => 'en_US.utf8'];
    }

    /**
     * Add metadata tags to FLAC files.
     *
     * @param string $file
     * @param array $tagData
     * @param string $coverFile
     * @return array
     */
    public function writeTags(string $file, array $tagData, string $coverFile = null): array
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file "' . $file . '" appears not to exist');
        }

        //Attempt to acquire the audio file's properties.

        $fileDetails = $this->validator->validateAudioFile($file, 'flac');

        //A counter to store the number of tags that we attempted to write.

        $numWritesAttempted = 0;

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

        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $this->command->input($file);

        $this->command->removeAll();

        $this->runProcess($this->command->compose());

        if (empty($tagData['date'][0]) || $tagData['date'][0] === 'Unknown') {
            unset($tagData['date']);
        }

        if (!empty($coverFile)) {
            $this->writeArtwork($coverFile);
        }

        $this->attemptWrite($file, $tagData);

        return $this->verifyTagData($file, $tagData);
    }

    public function removeAllTags(string $file): bool
    {
        $this->command->removeAllArguments();

        $this->command->input($file);

        $this->command->removeAll();

        $process = $this->runProcess($this->command->compose());

        // As of this writing, metaflac returns an exit status of
        // zero (which cannot necessarily be relied upon on Windows)
        // and does not produce any output on success. The latter fact is
        // far more reliable than the exit status.

        if ($process->getOutput() === '' && $process->getErrorOutput() === '') {
            return true;
        } else {
            return false;
        }
    }

    public function removeTags(array $data): bool
    {
        // TODO: Implement removeTags() method.
    }

    public function writeArtwork(string $audioFile, string $imageFile): bool
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $this->command->removeAllArguments();

        $this->command->input($audioFile);

        $this->command->importPicture($imageFile);

        $process = $this->runProcess($this->command->compose());

        //As of this writing, metaflac returns an exit status of
        //zero (which cannot necessarily be relied upon on Windows)
        //and does not produce any output on success. The latter fact is
        //far more reliable than the exit status.

        if ($process->getOutput() === '' && $process->getErrorOutput() === '') {
            return true;
        } else {
            return false;
        }
    }

    public function removeArtwork(string $file): bool
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $this->command->removeAllArguments();

        $this->command->input($file);

        $this->command->removeBlockType(['PICTURE']);

        $process = $this->runProcess($this->command->compose());

        //As of this writing, metaflac returns an exit status of
        //zero (which cannot necessarily be relied upon on Windows)
        //and does not produce any output on success. The latter fact is
        //far more reliable than the exit status.

        if ($process->getOutput() === '' && $process->getErrorOutput() === '') {
            return true;
        } else {
            return false;
        }
    }

    protected function runProcess(array $cmd): Process
    {
        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null, $this->env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
        );

        return $this->process;
    }

    protected function attemptWrite(string $file, array $tagData): void
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any
        // UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        // IMPORTANT: The --set-vc-field option is deprecated in favor of the
        // --set-tag option; using the deprecated option will cause the command to
        // fail on systems on which the option is not supported.

        $this->command->removeAllArguments();

        $this->command->input($file);

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                $this->command->setTag($fieldName, $fieldValue);
            }
        }

        $this->runProcess($this->command->compose());

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
        );
    }

    // TODO As it stands, this function is problematic because the Vorbis Comment
    // standard allows for multiple instances of the same tag name, e.g., passing
    // --set-tag=ARTIST=Foo --set-tag=ARTIST=Bar is perfectly valid. This function
    // should be modified to accommodate that fact.

    protected function verifyTagData(string $file, array $tagData)
    {
        $fileDetails = $this->getid3->analyze($file);

        // TODO Determine what this was used for and whether or not it needs to stay.

        //if ($allowBlank !== true) {

        $vorbiscomment = $fileDetails['tags']['vorbiscomment'];

        $failures = [];

        // Compare the passed tag data to the values acquired from the file.

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                if ($vorbiscomment[$fieldName][0] != $fieldValue) {
                    $failures[$vorbiscomment[$fieldName][0]];
                }
            }
        }

        // We're able to compare how many tags were written versus how
        // many write attempts were made in order to determine our
        // success rate.

        if (count($failures) === 0) {
            return ['result' => true, 'error' => null];
        } else {
            return [
                'result' => false,
                'error' => 'Expected does not match actual for tags:' . implode(', ', $failures)
            ];
        }

        //}
    }
}
