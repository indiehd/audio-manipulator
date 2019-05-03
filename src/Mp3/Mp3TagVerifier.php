<?php

namespace IndieHD\AudioManipulator\Mp3;

use getID3;

use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;

class Mp3TagVerifier implements TagVerifierInterface
{
    public function __construct(
        getID3 $getid3
    ) {
        $this->getid3 = $getid3;

        // This option is specific to the tag READER (the WRITER has its own,
        // separate encoding setting).

        $this->getid3->setOption(['encoding' => 'UTF-8']);
    }

    public function verify(string $file, array $tagData, array $fieldMappings = null): void
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
