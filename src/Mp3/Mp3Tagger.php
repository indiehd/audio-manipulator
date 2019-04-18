<?php

namespace IndieHD\AudioManipulator\Mp3;

use getID3;
use getid3_writetags;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use IndieHD\FilenameSanitizer\FilenameSanitizerInterface;
use IndieHD\AudioManipulator\Tagging\AudioTaggerException;
use IndieHD\AudioManipulator\Validation\AudioValidatorException;
use IndieHD\AudioManipulator\Processing\ProcessInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class Mp3Tagger implements TaggerInterface
{
    public function __construct(
        ValidatorInterface $validator,
        getID3 $getid3,
        getid3_writetags $writeTags,
        ProcessInterface $process,
        LoggerInterface $logger,
        FilenameSanitizerInterface $filenameSanitizer
    ) {
        $this->validator = $validator;
        $this->getid3 = $getid3;
        $this->writeTags= $writeTags;
        $this->process = $process;
        $this->logger = $logger;
        $this->filenameSanitizer = $filenameSanitizer;

        // This option is specific to the tag READER (the WRITER has its own,
        // separate encoding setting).

        $this->getid3->setOption(['encoding' => 'UTF-8']);

        // TODO Make the log location configurable.

        $fileHandler = new StreamHandler(
            'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR
            . 'mp3-tagger.log',
            Logger::INFO
        );

        $this->logger->pushHandler($fileHandler);

        $this->env = ['LC_ALL' => 'en_US.utf8'];
    }

    /**
     * Add metadata tags to an MP3 file. The $tagData input value should
     * be an array that was generated using Music::generateGetid3Tag().
     * Note that a couple  of small tag manipulations must occur
     * for the tag, which is created using the vorbiscomment standard,
     * to be suitable for an MP3 file.
     */
    public function writeTags(string $file, array $tagData, string $coverFile = null): void
    {
        $this->validator->validateAudioFile($file, 'mp3');

        $this->writeTags->filename = $file;

        $this->writeTags->tagformats = array('id3v2.4');

        $this->writeTags->overwrite_tags = true;

        // Certain applications cannot read UTF-8 tags, such as the Explorer
        // shell in Windows Vista.

        $this->writeTags->tag_encoding = 'UTF-8';

        $this->writeTags->remove_other_tags = true;

        // It's important that this comes before we handle the cover art, because
        // we don't want to include the cover as a write attempt (when we
        // read the tags back in to determine if they were written successfully, the
        // cover is not the among the tags, so the attempt vs. written values differ
        // thus triggering an error condition).

        $numWritesAttempted = count($tagData);

        if (!empty($coverFile)) {
            // Handle any cover art.

            $res = $this->prepareCoverImageForTag($coverFile);

            if ($res['result'] !== false) {
                list($APIC_width, $APIC_height, $apicImageTypeId) = getimagesize($coverFile);

                $mimeType = $apicImageTypeId;

                $tagData['attached_picture'][0]['data']          = $res['result'];
                $tagData['attached_picture'][0]['picturetypeid'] = 0x03;
                $tagData['attached_picture'][0]['description']   = '';
                $tagData['attached_picture'][0]['mime']          = 'image/jpeg';
            } else {
                $error = 'Embedding cover art in MP3 file ' . $file . ' failed; ' . $res['error'];

                throw new AudioTaggerException($error);
            }
        }

        $this->writeTags->tag_data = $tagData;

        $this->writeTags->WriteTags();

        // Re-read the file to ensure that the new ID3 tag values match the
        // supplied input values.

        $fileDetails = $this->getid3->analyze($file);

        $prefix = 'getID3\'s analyze() method';

        if (!is_array($fileDetails)) {
            $error = $prefix . ' did not return a usable array';

            throw new AudioTaggerException($error);
        }

        if (!isset($fileDetails['tags']['id3v2'])) {
            $error = $prefix . ' determined that the tags were not written for some reason';

            throw new AudioTaggerException($error);
        } else {
            // If at least one tag was written, we'll end-up here.

            $id3v2 = $fileDetails['tags']['id3v2'];

            // Before we compare the tag data that we fed to the tagging function
            // against the data that we just pulled off the newly-tagged file
            // (this ensures that all tags were written successfully), we must
            // first manipulate a handful of the key names.

            // When the tags are written, getID3 makes format-specific changes
            // to the key names that are provided as input. For this reason, when
            // reading the tag data back in for comparison, we have to account
            // for any key names that getID3 changed to suit the target tag format.

            // We don't want to include artwork data when checking the success of
            // the other tag-writes.

            if (isset($tagData['attached_picture'])) {
                unset($tagData['attached_picture']);
            }

            $numWritesSucceeded = 0;

            // Now that any format-specific key names were changed back to
            // the generic forms that GetID3 expects, we'll compare each
            // tag on the file with the tag data that we attempted to apply
            // earlier, in order to determine whether or not each tag was
            // written successfully.

            foreach ($tagData as $fieldName => $fieldDataArray) {
                foreach ($fieldDataArray as $numericIndex => $fieldValue) {
                    if ($id3v2[$fieldName][0] == $fieldValue) {
                        $numWritesSucceeded++;
                    }
                }
            }

            // We're able to compare how many tags were written versus how
            // many write attempts were made in order to determine our
            // success rate.

            if ($numWritesAttempted != $numWritesSucceeded) {
                $error = 'The number of tag writes that succeeded ('
                    . $numWritesSucceeded . ') is less than the number attempted ('
                    . $numWritesAttempted . ')';

                throw new AudioTaggerException($error);
            }
        }
    }

    public function removeAllTags(string $file): void
    {
        // TODO: Implement removeAllTags() method.
    }

    public function removeTags(string $file, array $tagData): void
    {
        // TODO: Implement removeTags() method.
    }

    public function writeArtwork(string $audioFile, string $imageFile): void
    {
        // TODO: Implement writeArtwork() method.
    }

    public function removeArtwork(string $file): void
    {
        // TODO: Implement removeArtwork() method.
    }

    /**
     * Largely from the getID3 Write demo.
     *
     * @param $imageFile
     * @return array
     */
    protected function prepareCoverImageForTag($imageFile)
    {
        ob_start();

        if ($fd = fopen($imageFile, 'rb')) {
            ob_end_clean();

            $apicData = fread($fd, filesize($imageFile));

            fclose($fd);

            list($APIC_width, $APIC_height, $apicImageTypeId) = getimagesize($imageFile);

            $imageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

            if (isset($imageTypes[$apicImageTypeId])) {
                return array('result' => $apicData, 'error' => null);
            } else {
                $error = 'Invalid image format (with APIC image type ID '
                    . $apicImageTypeId . ') (only GIF, JPEG, and PNG are supported)';

                return array('result' => false, 'error' => $error);
            }
        } else {
            ob_end_clean();
            $error = 'Cannot open ' . $imageFile . ': ' . ob_get_contents();
            return array('result' => false, 'error' => $error);
        }
    }
}
