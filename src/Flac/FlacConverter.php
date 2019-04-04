<?php

namespace IndieHD\AudioManipulator\Flac;

use Psr\Log\LoggerInterface;

use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\AudioManipulator\Mp3\Mp3WriterInterface;
use IndieHD\AudioManipulator\Alac\AlacWriterInterface;
use IndieHD\AudioManipulator\Wav\WavWriterInterface;

class FlacConverter implements
    ConverterInterface,
    Mp3WriterInterface,
    AlacWriterInterface,
    WavWriterInterface
{
    private $validator;
    private $process;
    private $logger;

    protected $supportedOutputFormats;

    public function __construct(
        ValidatorInterface $validator,
        ProcessInterface $process,
        LoggerInterface $logger
    ) {
        $this->validator = $validator;
        $this->process = $process;
        $this->logger = $logger;

        $this->supportedOutputFormats = [
            'wav',
            'mp3',
            'm4a',
            'ogg',
        ];
    }

    public function setSupportedOutputFormats(array $supportedOutputFormats): void
    {
        $this->supportedOutputFormats = $supportedOutputFormats;
    }

    public function validateFile($inputFile): array
    {
        if (!file_exists($inputFile)) {
            throw new FileNotFoundException('The input file "' . $inputFile . '" appears not to exist');
        }

        // Grab the file extension to determine the implicit audio format of the
        // input file.

        $fileExt = pathinfo($inputFile, PATHINFO_EXTENSION);
        $inputFormat = $fileExt;

        // Attempt to validate the input file according to its implied file type.

        // The audio type validation function returns the audio file's
        // details if the validation succeeds. We may as well leverage that
        // for the next step.

        return $this->validator->validateAudioFile($inputFile, $inputFormat);
    }

    public function writeFile(string $inputFile, string $outputFile): array
    {
        $fileDetails = $this->validateFile($inputFile);

        /*
        // These toggles default to true, but may be changed hereafter, depending
        // on the totality of the inputs supplied.

        $performTrim = true;
        $canFadeIn = true;
        $canFadeOut = true;

        // Determine whether or not the audio file is actually shorter than
        // the specified clip length. This is done to prevent the resultant
        // file from being padded with silence.

        if ($fileDetails['playtime_seconds'] < $clipLength) {
            // No trim or fades are necessary if the play-time
            // is less than the specified clip length.

            $performTrim = false;
            $canFadeIn = false;
            $canFadeOut = false;
        }

        // This block prevents problems from a track preview start
        // time that is too far into the track to allow for a preview clip of
        // the specified length.

        if (($fileDetails['playtime_seconds'] - $trimStartTime) < $clipLength) {
            // We'll force the track preview to start exactly $clipLength seconds
            // before the end of the track, thus forcing a $clipLength-second preview
            // clip length.

            $trimStartTime = $fileDetails['playtime_seconds'] - $clipLength;
        }

        // Convert the clip length from seconds to hh:mm:ss format.

        $clipLength = Utility::sec2hms($clipLength, false, false);
        */

        $cmd = [];

        // Attempt to perform the transcoding operation.

        $cmd[] = 'sox';

        // TODO Deal with this.

        #if ($this->singleThreaded === true) {
            $cmd[] = ' --single-threaded';
        #}

        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd[] = ' -V4';
        $cmd[] = ' ' . escapeshellarg($inputFile);
        $cmd[] = ' --channels 2';
        $cmd[] = ' ' . escapeshellarg($outputFile);

        // TODO Deal with this.

        /*
        if ($performTrim !== false) {
            $cmd[] = ' trim ' . escapeshellarg($trimStartTime) . ' ' . escapeshellarg($clipLength);
        }

        if ($canFadeIn !== false || $canFadeOut !== false) {
            $cmd[] = ' fade q ';

            if ($canFadeIn === false) {
                // Setting a fade-in length of zero in SoX is the
                // same as having no fade-in at all.

                $fadeInLength = '0';
            }

            $cmd[] = escapeshellarg($fadeInLength);

            if ($canFadeOut !== false) {
                $cmd[] = ' ' . escapeshellarg($clipLength) . ' ' . escapeshellarg($fadeOutLength);
            }
        }
        */

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null, $env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
                . $this->process->getOutput()
        );

        // On the Windows platform, SoX's exit status is not preserved, thus
        // we must confirm that the operation was completed successfully by
        // other means.

        // We'll use a validation function to analyze the resultant file and ensure that the
        // file meets our expectations.

        // Grab the file extension to determine the implicit audio format of the
        // output file.

        $fileExt = pathinfo($outputFile, PATHINFO_EXTENSION);

        $outputFormat = $fileExt;

        $newFileDetails = $this->validator->validateAudioFile($outputFile, $outputFormat);

        if (!$newFileDetails) {
            throw new InvalidAudioFileException('The ' . strtoupper($outputFormat)
                . ' file appears to have been created, but does not validate as'
                . ' such; ensure that the determined audio format (e.g., MP1,'
                . ' MP2, etc.) is in the array of allowable formats');
        }

        return $newFileDetails;
    }

    public function toMp3(string $inputFile, string $outputFile): array
    {
        return $this->writeFile($inputFile, $outputFile);
    }

    /**
     * Important: NEVER call this function on a "master" file, as it removes the
     * artwork from THAT file (and not a copy)!
     * @param string $inputFile
     * @param string $outputFile
     * @return array
     */
    public function toAlac(string $inputFile, string $outputFile): array
    {
        $fileDetails = $this->validateFile($inputFile);

        //In avconv/ffmpeg version 9.16 (and possibly earlier), embedded artwork with a
        //width or height that is not divisible by 2 will cause a failure, e.g.:
        //"width not divisible by 2 (1419x1419)". So, we must strip any "odd" artwork.
        //It's entirely possible that artwork was not copied in earlier versions, so
        //this error did not occur.

        // TODO Determine whether or not this is still necessary.

        //$this->tagger->removeArtwork($inputFile);

        $cmd = [];

        // The "-y" switch forces overwriting.

        $cmd[] = 'ffmpeg';
        $cmd[] = '-y';
        $cmd[] = '-i';

        //Tag data is copied automatically. Nice!!!

        $pathParts = pathinfo($inputFile);

        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd[] = escapeshellarg($inputFile);
        $cmd[] = '-acodec';
        $cmd[] = 'alac';
        $cmd[] = escapeshellarg($outputFile);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null, $env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
        );

        // We'll use a validation function to analyze the resultant file and ensure that the
        // file meets our expectations.

        // Grab the file extension to determine the implicit audio format of the
        // input file.

        $fileExt = pathinfo($outputFile, PATHINFO_EXTENSION);

        $outputFormat = $fileExt;

        $newFileDetails = $this->validator->validateAudioFile($outputFile, $outputFormat);

        if (!$newFileDetails) {
            throw new InvalidAudioFileException('The ' . strtoupper($outputFormat)
                . ' file appears to have been created, but does not validate as'
                . ' such; ensure that the determined audio format (e.g., MP1,'
                . ' MP2, etc.) is in the array of allowable formats');
        }

        return $newFileDetails;
    }

    public function toWav(string $inputFile, string $outputFile): array
    {
        return $this->writeFile($inputFile, $outputFile);
    }

    public function applyEffect($effect): bool
    {
        // TODO: Implement applyEffect() method.
    }
}
