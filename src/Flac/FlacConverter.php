<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Logging\LoggerInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Converting\ConverterInterface;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\AudioManipulator\Mp3\Mp3WriterInterface;
use IndieHD\AudioManipulator\Alac\AlacWriterInterface;
use IndieHD\AudioManipulator\Wav\WavWriterInterface;
use IndieHD\AudioManipulator\CliCommand\SoxCommandInterface;
use IndieHD\AudioManipulator\CliCommand\FfmpegCommandInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;
use IndieHD\AudioManipulator\Processing\Process;

class FlacConverter implements
    ConverterInterface,
    Mp3WriterInterface,
    AlacWriterInterface,
    WavWriterInterface
{
    private $validator;
    private $process;
    private $logger;
    private $sox;
    private $ffmpeg;

    protected $supportedOutputFormats;

    public function __construct(
        ValidatorInterface $validator,
        ProcessInterface $process,
        LoggerInterface $logger,
        SoxCommandInterface $sox,
        FfmpegCommandInterface $ffmpeg
    ) {
        $this->validator = $validator;
        $this->process = $process;
        $this->logger = $logger;
        $this->sox = $sox;
        $this->ffmpeg = $ffmpeg;

        $this->setSupportedOutputFormats([
            'wav',
            'mp3',
            'm4a',
            'ogg',
        ]);

        $this->logger->configureLogger('FLAC_CONVERTER_LOG');
    }

    public function setSupportedOutputFormats(array $supportedOutputFormats): void
    {
        $this->supportedOutputFormats = $supportedOutputFormats;
    }

    protected function runProcess(array $cmd, CliCommandInterface $command): Process
    {
        $this->process->setCommand($cmd);

        $this->process->setTimeout(600);

        $this->process->run(null);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->log(
            $this->process->getProcess()->getCommandLine() . PHP_EOL . PHP_EOL
            . $this->process->getOutput()
        );

        $command->removeAllArguments();

        return $this->process;
    }

    private function writeFile(string $inputFile, string $outputFile): array
    {
        $this->validator->validateAudioFile($inputFile, 'flac');

        $this->sox
            ->input($inputFile)
            ->output($outputFile);

        $this->runProcess($this->sox->compose(), $this->sox);

        // On the Windows platform, SoX's exit status is not preserved, thus
        // we must confirm that the operation was completed successfully by
        // other means.

        // We'll use a validation function to analyze the resultant file and ensure that the
        // file meets our expectations.

        // Grab the file extension to determine the implicit audio format of the
        // output file.

        $fileExt = pathinfo($outputFile, PATHINFO_EXTENSION);

        $outputFormat = $fileExt;

        return $this->validator->validateAudioFile($outputFile, $outputFormat);
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
        $this->validator->validateAudioFile($inputFile, 'flac');

        // In avconv/ffmpeg version 9.16 (and possibly earlier), embedded artwork with a
        // width or height that is not divisible by 2 will cause a failure, e.g.:
        // "width not divisible by 2 (1419x1419)". So, we must strip any "odd" artwork.
        // It's entirely possible that artwork was not copied in earlier versions, so
        // this error did not occur.

        // TODO Determine whether or not this is still necessary.

        #$this->tagger->removeArtwork($inputFile);

        $this->ffmpeg
            ->input($inputFile)
            ->output($outputFile)
            ->overwriteOutput($outputFile)
            ->forceAudioCodec('alac');

        $this->runProcess($this->ffmpeg->compose(), $this->ffmpeg);

        // We'll use a validation function to analyze the resultant file and ensure that the
        // file meets our expectations.

        // Grab the file extension to determine the implicit audio format of the
        // input file.

        $fileExt = pathinfo($outputFile, PATHINFO_EXTENSION);

        $outputFormat = $fileExt;

        return $this->validator->validateAudioFile($outputFile, $outputFormat);
    }

    public function toWav(string $inputFile, string $outputFile): array
    {
        return $this->writeFile($inputFile, $outputFile);
    }
}
