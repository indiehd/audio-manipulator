<?php

namespace IndieHD\AudioManipulator\Validation;

interface ValidatorInterface
{
    /**
     * Accepts an audio file as input and ensures that the file is of the
     * type specified.
     *
     * @param string $file
     * @param string $type
     *
     * @return array
     */
    public function validateAudioFile(string $file, string $type): array;
}
