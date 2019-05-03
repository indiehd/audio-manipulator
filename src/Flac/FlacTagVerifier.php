<?php

namespace IndieHD\AudioManipulator\Flac;

use getID3;

use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;

class FlacTagVerifier implements TagVerifierInterface
{
    public function __construct(
        getID3 $getid3
    ) {
        $this->getid3 = $getid3;

        // This option is specific to the tag READER (the WRITER has its own,
        // separate encoding setting).

        $this->getid3->setOption(['encoding' => 'UTF-8']);
    }

    // TODO As it stands, this function is problematic because the Vorbis Comment
    // standard allows for multiple instances of the same tag name, e.g., passing
    // --set-tag=ARTIST=Foo --set-tag=ARTIST=Bar is perfectly valid. This function
    // should be modified to accommodate that fact.

    public function verify(string $file, array $tagData, array $fieldMappings = null): void
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
                    $failures[] = $fieldName;
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
