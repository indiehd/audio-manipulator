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

        $this->process->setCommand($this->command->compose());

        $this->process->setTimeout(600);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8
        // character will appear as a "#" symbol.

        $this->process->run(null, $this->env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
        );

        // Attempt to acquire the audio file's properties, again, now that
        // we've attempted to remove any existing tags.

        $fileDetails = $this->getid3->analyze($file);

        // We attempted to remove all tags from the FLAC file; we can
        // determine whether or not we were successful in that effort by
        // checking to see if the vorbiscomment block is present in the file.

        if (isset($fileDetails['tags']['vorbiscomment'])) {
            throw new AudioTaggerException('The vorbiscomment block was not removed for some reason');
        }

        if (empty($tagData['date'][0]) || $tagData['date'][0] === 'Unknown') {
            unset($tagData['date']);
        }

        if (!empty($coverFile)) {
            $this->writeArtwork($coverFile);
        }

        // Attempt to add each tag to the FLAC file, and keep track of the number
        // of write attempts. Given that the shell_exec() exit status is not a
        // reliable means by which to determine the success/failure of the
        // operation, we must compare the number of write attempts to the number
        // of tags that exist once we're done attempting to write.

        // TODO Wouldn't it make more sense simply to compare the values
        // that were attempted with the actual values? Then all tags could be
        // written in a single command, rather than incur the overhead of
        // starting an individual process for each field.

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any
                // UTF-8 character will equate to an empty string.

                setlocale(LC_CTYPE, 'en_US.UTF-8');

                // IMPORTANT: The --set-vc-field option is deprecated in favor of the
                // --set-tag option; using the deprecated option will cause the command to
                // fail on systems on which the option is not supported.

                $this->command->removeAllArguments();

                $this->command->input($file);

                $this->command->setTag($fieldName, $fieldValue);

                $this->process->setCommand($this->command->compose());

                $this->process->setTimeout(600);

                // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8
                // character will appear as a "#" symbol.

                $this->process->run(null, $this->env);

                if (!$this->process->isSuccessful()) {
                    throw new ProcessFailedException($this->process);
                }

                $this->logger->info(
                    $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
                    . $this->process->getOutput()
                );

                $numWritesAttempted++;
            }
        }

        // Attempt to acquire the audio file's properties, again, now that we've
        // attempted to write new tags.

        $fileDetails = $this->getid3->analyze($file);

        $prefix = 'getID3\'s analyze() method';

        // TODO Determine what this was used for and whether or not it needs to stay.

        //if ($allowBlank !== true) {
        if (!isset($fileDetails['tags']['vorbiscomment'])) {
            $error = $prefix . ' determined that the tags were not written for some reason';

            if (!empty($fileDetails['error'])) {
                for ($i = 0; $i < count($fileDetails['error']); $i++) {
                    if ($i == 0) {
                        $error .= ": ";
                    } else {
                        $error .= '; ';
                    }

                    $error .= $fileDetails['error'][$i];
                }
            }

            return array('result' => false, 'error' => $error);
        } else {
            // If at least one tag was written, we'll end-up here.

            $vorbiscomment = $fileDetails['tags']['vorbiscomment'];

            $numWritesSucceeded = 0;

            // Now, we'll compare each tag on the file with the tag data that
            // we attempted to apply earlier, in order to determine whether
            // or not each tag was written successfully.

            foreach ($tagData as $fieldName => $fieldDataArray) {
                foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                    if ($vorbiscomment[$fieldName][0] == $fieldValue) {
                        $numWritesSucceeded++;
                    }
                }
            }

            // We're able to compare how many tags were written versus how
            // many write attempts were made in order to determine our
            // success rate.

            if ($numWritesAttempted == $numWritesSucceeded) {
                return array('result' => true, 'error' => null);
            } else {
                $error = 'The number of tag writes that succeeded ('
                    . $numWritesSucceeded . ') is less than the number attempted ('
                    . $numWritesAttempted . ')';

                return array('result' => false, 'error' => $error);
            }
        }
        //} else {
        //    return array('result' => true, 'error' => null);
        //}
    }

    public function removeTags(array $data)
    {
        // TODO: Implement removeTags() method.
    }

    public function writeArtwork(string $imagePath)
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd = 'metaflac --import-picture-from=' . escapeshellarg($imagePath) . ' ' . escapeshellarg($audioFile);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $res = \GlobalMethods::openProcess($cmd, null, $env);

        if ($res !== false) {
            // As of this writing, metaflac returns an exit status of
            // zero (which cannot necessarily be relied upon on Windows)
            // and does not produce any output on success. The latter fact is
            // far more reliable than the exit status.

            if ($res['stdOut'] == '' && $res['stdErr'] == '') {
                return array('result' => true, 'error' => null);
            } else {
                return [
                    'result' => false,
                    'error' => 'The call to `metaflac` produced output, which'
                        . ' indicates an error condition: ' . \Utility::varToString($res)
                ];
            }
        } else {
            return array('result' => false, 'error' => 'The process could not be opened: ' . $cmd);
        }
    }

    public function removeArtwork()
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd = 'metaflac --remove --block-type=PICTURE ' . escapeshellarg($file);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setTimeout(600);

        $this->process->run($cmd, null, $env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info($cmd . PHP_EOL . PHP_EOL . $this->process->getOutput());

        //As of this writing, metaflac returns an exit status of
        //zero (which cannot necessarily be relied upon on Windows)
        //and does not produce any output on success. The latter fact is
        //far more reliable than the exit status.

        if ($this->process->getOutput() === '' && $this->process->getErrorOutput() === '') {
            return ['result' => true, 'error' => null];
        } else {
            return [
                'result' => false,
                'error' => 'The call to `metaflac` produced output, which'
                    . ' indicates an error condition: (stdout)' . $this->process->getOutput()
                    . ' (stderr) ' . $this->process->getErrorOutput()
            ];
        }
    }

    protected function runProcess(array $cmd)
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
    }
}
