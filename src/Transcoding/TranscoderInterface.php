<?php

namespace IndieHD\AudioManipulator\Transcoding;

interface TranscoderInterface
{
    /**
     * @param string $inputFile
     * @param string $outputFile
     * @param float $trimStartTime How many seconds into the track the trimming will begin.
     * @param float $clipLength How many seconds in length the resultant track shall be.
     * @param float $fadeInLength Over what period of seconds the audio will fade-in.
     * @param float $fadeOutLength Over what period of seconds the audio will fade-out.
     * @return array $fileDetails The audio-related details of the resultant file.
     * @throws InvalidAudioFileException
     */
    public function transcode(
        string $inputFile,
        string $outputFile,
        float $trimStartTime = 0,
        float $clipLength = 90,
        float $fadeInLength = 0,
        float $fadeOutLength = 0
    );
}
