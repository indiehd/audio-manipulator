<?php

namespace IndieHD\AudioManipulator\Flac;

use getID3;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Tagging\TagVerifierInterface;

class FlacTagVerifier implements TagVerifierInterface
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
                if (!isset($vorbiscomment[$fieldName][0])) {
                    $failures[] = $fieldName.' tag does not exist on tagged file';
                } else if ($vorbiscomment[$fieldName][0] != $fieldValue) {
                    $failures[] = 'Expected value "'. $fieldValue . '" does not match actual value "' . $vorbiscomment[$fieldName][0] . '" for tag "' . $fieldName . '")';
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
