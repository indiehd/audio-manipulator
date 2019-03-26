<?php

namespace IndieHD\AudioManipulator\Validation;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use IndieHD\AudioManipulator\MediaParsing\MediaParserInterface;
use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;

class Validator implements ValidatorInterface
{
    public function __construct(
        MediaParserInterface $mediaParser
    ) {
        $this->mediaParser = $mediaParser;
    }

    /**
     * @inheritdoc
     * @throws \IndieHD\AudioManipulator\Validation\InvalidAudioFileException
     */
    public function validateAudioFile(string $file, string $type): array
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException('The input file does not exist');
        }

        $validTypes = [
            'wav',
            'flac',
            'mp3'
        ];

        if (!in_array($type, $validTypes)) {
            throw new InvalidAudioFileException('A valid audio file type was not specified');
        }

        $fileDetails = $this->mediaParser->analyze($file);

        if (!is_array($fileDetails)) {
            throw new InvalidAudioFileException('Media parser\'s analyze() method did not return usable data');
        }

        if ($type === 'wav') {
            // When certain FLAC files are converted to WAV files, the dataformat may
            // be "mp1" or "mp2" instead of "wav".
            // Both data formats are lossless, and therefore acceptable.

            $acceptableWavFormats = [
                'wav',
                'mp1',
                'mp2',
            ];

            if (!isset($fileDetails['audio']['dataformat'])
                || !in_array($fileDetails['audio']['dataformat'], $acceptableWavFormats)
            ) {
                throw new InvalidAudioFileException(
                    'The audio file\'s ("' . $file . '") data format could not'
                        . ' be ascertained, or the format was not within the'
                        . ' acceptable list of WAV audio data formats (format was "'
                        . $fileDetails['audio']['dataformat'] . '")'
                );
            }
        } elseif ($type === 'flac' || $type === 'mp3') {
            if (!isset($fileDetails['fileformat'])
                || $fileDetails['fileformat'] != $type
            ) {
                throw new InvalidAudioFileException(
                    'The audio file\'s file format could not be ascertained or'
                        . 'is not of the specified type (' . $type . ')'
                );
            }
        }

        return $fileDetails;
    }
}
