<?php

namespace IndieHD\AudioManipulator\Alac;

use getID3;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;

class AlacTagVerifier implements TagVerifierInterface
{
    public getID3 $getid3;

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

        $tagsOnFile = $fileDetails['tags']['quicktime'];

        $failures = [];

        // Compare the passed tag data to the values acquired from the file.

        foreach ($tagData as $fieldName => $fieldDataArray) {
            foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                if (isset($fieldMappings[$fieldName])) {
                    $fieldName = $fieldMappings[$fieldName];
                }

                if (!isset($tagsOnFile[$fieldName][0])) {
                    $failures[] = $fieldName.' tag does not exist on tagged file';
                } elseif ($tagsOnFile[$fieldName][0] != $fieldValue) {
                    $failures[] = 'Expected value "'. $fieldValue . '" does not match actual value "' . $tagsOnFile[$fieldName][0] . '" for tag "' . $fieldName . '")';
                }
            }
        }

        if (count($failures) > 0) {
            throw new AudioTaggerException(
                'Tagging failures occurred: '.implode(', ', $failures)
            );
        }
    }
}
