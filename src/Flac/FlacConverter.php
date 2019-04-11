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
use IndieHD\AudioManipulator\Flac\FlacEffectInterface;
use IndieHD\AudioManipulator\Alac\AlacEffectInterface;

class FlacConverter implements
    ConverterInterface,
    Mp3WriterInterface,
    AlacWriterInterface,
    WavWriterInterface
{
    private $validator;
    private $process;
    private $logger;
    private $flacEffect;
    private $alacEffect;

    protected $supportedOutputFormats;

    public function __construct(
        ValidatorInterface $validator,
        ProcessInterface $process,
        LoggerInterface $logger,
        FlacEffectInterface $flacEffect,
        AlacEffectInterface $alacEffect
    ) {
        $this->validator = $validator;
        $this->process = $process;
        $this->logger = $logger;
        $this->flacEffect = $flacEffect;
        $this->alacEffect = $alacEffect;

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
        return $this->validator->validateAudioFile($inputFile, 'flac');
    }

    public function writeFile(string $inputFile, string $outputFile): array
    {
        $this->flacEffect->command->input($inputFile);

        $this->flacEffect->command->output($outputFile);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8
        // character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setCommand($this->flacEffect->getCommand()->compose());

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
     * @param string $inputFile
     * @param string $outputFile
     * @return array
     */
    public function toAlac(string $inputFile, string $outputFile): array
    {
        $this->validateFile($inputFile);

        // In avconv/ffmpeg version 9.16 (and possibly earlier), embedded artwork with a
        // width or height that is not divisible by 2 will cause a failure, e.g.:
        // "width not divisible by 2 (1419x1419)". So, we must strip any "odd" artwork.
        // It's entirely possible that artwork was not copied in earlier versions, so
        // this error did not occur.

        // TODO Determine whether or not this is still necessary.

        #$this->tagger->removeArtwork($inputFile);

        $this->alacEffect->command->input($inputFile);

        $this->alacEffect->command->output($outputFile);

        $this->alacEffect->command->overwriteOutput($outputFile);

        $this->alacEffect->command->forceAudioCodec('alac');

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8
        // character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setCommand($this->alacEffect->getCommand()->compose());

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
}
