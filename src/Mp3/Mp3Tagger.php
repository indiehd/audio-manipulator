<?php

namespace IndieHD\AudioManipulator\Mp3;

use getID3;
use getid3_writetags;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\FilenameSanitizer\FilenameSanitizerInterface;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;
use IndieHD\AudioManipulator\CliCommand\LameCommandInterface;

class Mp3Tagger implements TaggerInterface
{
    public function __construct(
        getID3 $getid3,
        getid3_writetags $writeTags,
        ProcessInterface $process,
        LoggerInterface $logger,
        FilenameSanitizerInterface $filenameSanitizer,
        LameCommandInterface $command,
        ValidatorInterface $validator
    ) {

        $this->getid3 = $getid3;
        $this->writeTags= $writeTags;
        $this->process = $process;
        $this->logger = $logger;
        $this->filenameSanitizer = $filenameSanitizer;
        $this->command = $command;
        $this->validator = $validator;

        // This option is specific to the tag READER (the WRITER has its own,
        // separate encoding setting).

        $this->getid3->setOption(['encoding' => 'UTF-8']);

        // TODO Make the log location configurable.

        $fileHandler = new StreamHandler(
            'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR
            . 'mp3-tagger.log',
            Logger::INFO
        );

        $this->logger->pushHandler($fileHandler);

        $this->env = ['LC_ALL' => 'en_US.utf8'];
    }

    public function writeTags(string $file, array $tagData): void
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file "' . $file . '" appears not to exist');
        }

        $this->command->input($file);

        $this->attemptWrite($file, $tagData);

        $this->verifyTagData($file, $tagData);
    }

    /*
    public function removeAllTags(string $file): void
    {
        // TODO: Implement removeAllTags() method.
    }
    */

    /*
    public function removeTags(string $file, array $tagData): void
    {
        // TODO: Implement removeTags() method.
    }
    */

    public function writeArtwork(string $audioFile, string $imageFile): void
    {
        // TODO: Implement writeArtwork() method.
    }

    public function removeArtwork(string $file): void
    {
        // TODO: Implement removeArtwork() method.
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

        $this->command->removeAllArguments();

        return $this->process;
    }

    protected function attemptWrite(string $file, array $tagData): void
    {
        // IMPORTANT: The --set-vc-field option is deprecated in favor of the
        // --set-tag option; using the deprecated option will cause the command to
        // fail on systems on which the option is not supported.

        $this->command->input($file);

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                $this->command->{'set' . ucfirst($fieldName)}($fieldValue);
            }
        }

        $this->runProcess($this->command->compose());
    }

    // TODO As it stands, this function is problematic because the Vorbis Comment
    // standard allows for multiple instances of the same tag name, e.g., passing
    // --set-tag=ARTIST=Foo --set-tag=ARTIST=Bar is perfectly valid. This function
    // should be modified to accommodate that fact.

    protected function verifyTagData(string $file, array $tagData): void
    {
        $fileDetails = $this->getid3->analyze($file);

        $tagsOnFile = $fileDetails['tags']['id3v2'];

        $failures = [];

        // Compare the passed tag data to the values acquired from the file.

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                if ($tagsOnFile[$fieldName][0] != $fieldValue) {
                    $failures[] = $fieldName;
                }
            }
        }

        if (count($failures) > 0) {
            throw new AudioTaggerException(
                'Expected value does not match actual value for tags:' . implode(', ', $failures)
            );
        }
    }

    /**
     * Largely from the getID3 Write demo.
     *
     * @param $imageFile
     * @return array
     */
    protected function prepareCoverImageForTag($imageFile)
    {
        ob_start();

        if ($fd = fopen($imageFile, 'rb')) {
            ob_end_clean();

            $apicData = fread($fd, filesize($imageFile));

            fclose($fd);

            list($APIC_width, $APIC_height, $apicImageTypeId) = getimagesize($imageFile);

            $imageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

            if (isset($imageTypes[$apicImageTypeId])) {
                return array('result' => $apicData, 'error' => null);
            } else {
                $error = 'Invalid image format (with APIC image type ID '
                    . $apicImageTypeId . ') (only GIF, JPEG, and PNG are supported)';

                return array('result' => false, 'error' => $error);
            }
        } else {
            ob_end_clean();
            $error = 'Cannot open ' . $imageFile . ': ' . ob_get_contents();
            return array('result' => false, 'error' => $error);
        }
    }
}
