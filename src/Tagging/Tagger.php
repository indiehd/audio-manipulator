<?php namespace IndieTorrent\AudioManipulator\Tagging;

use \getID3;
use \getid3_writetags;

class Tagger
{

public function __construct(
	getID3 $getid3,
	getid3_writetags $writeTags
)
{
	$this->getId3 = $getid3;
	$this->writeTags= $writeTags;
}


//Add a metadata tags to an MP3 file. The $tagData input value should
//be an array that was generated using Music::generateGetid3Tag().
//Note that a couple  of small tag manipulations must occur
//for the tag, which is created using the vorbiscomment standard,
//to be suitable for an MP3 file.
function tagMp3File($file, $tagData, $coverFile = NULL) {
	if (!file_exists($file)) {
		$error = 'The input file appears not to exist';
		return array('result' => FALSE, 'error' => $error);
	}
	
	if (!is_array($tagData)) {
		$error = 'The tag data must be supplied as an array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	$gid3 = new getID3;
	
	if (!is_object($gid3)) {
		$error = 'The getID3 object could not be instantiated';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//XXX This is commented-out because we're using UTF-8 (where's that code?).
	$gid3->setOption(array('encoding'=>'UTF-8'));
	
	//Analyze the input file in order to determine which tag types
	//are supported for the audio file.
	$fileDetails = $gid3->analyze($file);
	if (!is_array($fileDetails)) {
		$error = 'getID3\'s analyze() method did not return a usable array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//XXX What was the original idea behind this? Can it be deleted?
	#getid3_lib::CopyTagsToComments($fileDetails);
	
	//Ensure that the file on which we're attempting to operate is indeed
	//an MP3 file.
	//
	//Note: we could use the self::validateAudioFile() method, but there's
	//no reason to waste the time/memory required to call that function
	//when we have to call getID3's analyze() method again.
	
	if (!isset($fileDetails['fileformat']) || $fileDetails['fileformat'] != 'mp3') {
		$error = 'The audio file does not validate as an MP3 file';
		return array('result' => FALSE, 'error' => $error);
	}
	
	/*
	//This block was taken directly from the 'demo.write.php' file supplied
	//with getID3; a few modifications were made to the variable names.
	switch ($fileDetails['fileformat']) {
		case 'mp3':
		case 'mp2':
		case 'mp1':
			$validTagTypes = array('id3v1', 'id3v2.3', 'ape');
			break;

		case 'mpc':
			$validTagTypes = array('ape');
			break;

		case 'ogg':
			if (@$fileDetails['audio']['dataformat'] == 'flac') {
				//$validTagTypes = array('metaflac');
				//metaflac doesn't (yet) work with OggFLAC files.
				$validTagTypes = array();
			} else {
				$validTagTypes = array('vorbiscomment');
			}
			break;

		case 'flac':
			$validTagTypes = array('metaflac');
			break;

		case 'real':
			$validTagTypes = array('real');
			break;

		default:
			$validTagTypes = array();
			break;
	}
	*/
	
	//A couple of small, ID3v2-specific changes to the tag format, since the input
	//format was originally intended for FLAC and OggVorbis files,
	//which are tagged using metaflac.
	
	$tagData['comment'][0] = $tagData['description'][0];
	unset($tagData['description']);
	if (!empty($tagData['date'][0]) && $tagData['date'][0] !== 'Unknown') {
		$tagData['recording_time'][0] = $tagData['date'][0];
	}
	unset($tagData['date']);
	$tagData['part_of_a_set'][0] = $tagData['discnumber'][0];
	unset($tagData['discnumber']);
	
	$tagWriter = $this->writeTags;
	
	$tagWriter->filename = $file;
	$tagWriter->tagformats = array('id3v2.4');
	$tagWriter->overwrite_tags = TRUE;
	//Certain applications cannot read UTF-8 tags,
	//such as the Explorer shell in Windows Vista.
	$tagWriter->tag_encoding = 'UTF-8';
	//We'll use ISO-8859-1' instead (required for
	//the ID3v1 spec, even though we're writing v2 tags).
	#$tagWriter->tag_encoding = 'ISO-8859-1';
	$tagWriter->remove_other_tags = TRUE;
	
	//It's important that this comes before we handle the cover art, because
	//we don't want to include the cover as a write attempt (when we
	//read the tags back in to determine if they were written successfully, the
	//cover is not the among the tags, so the attempt vs. written values differ
	//thus triggering an error condition).
	$numWritesAttempted = count($tagData);
	
	if (!empty($coverFile)) {
		//Handle any cover art.
		
		$res = $this->prepareCoverImageForTag($coverFile);
		
		if ($res['result'] !== FALSE) {
			list($APIC_width, $APIC_height, $apicImageTypeId) = getimagesize($coverFile);
			
			$mimeType = $apicImageTypeId;
		
			$tagData['attached_picture'][0]['data']          = $res['result'];
			$tagData['attached_picture'][0]['picturetypeid'] = 0x03;
			$tagData['attached_picture'][0]['description']   = '';
			$tagData['attached_picture'][0]['mime']          = 'image/jpeg';
		}
		else {
			$error = 'Embedding cover art in MP3 file ' . $file . ' failed; ' . $res['error'];
			return array('result' => FALSE, 'error' => $error);
		}
	}
	
	$tagWriter->tag_data = $tagData;

	if (!$tagWriter->WriteTags()) {
		/*
		echo '<pre>';
		@print_r($tagWriter->warnings);
		@print_r($tagWriter->errors);
		echo '</pre>';
		*/
	}
	
	//Re-read the file to ensure that the new ID3 tag values
	//match the supplied input values.
	$fileDetails = $gid3->analyze($file);

	$prefix = 'getID3\'s analyze() method';
	
	if (!is_array($fileDetails)) {
		$error = $prefix . ' did not return a usable array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	if (!isset($fileDetails['tags']['id3v2'])) {
		$error = $prefix . ' determined that the tags were not written for some reason';
		return array('result' => FALSE, 'error' => $error);
	}
	else {
		//If at least one tag was written, we'll end-up here.
		$id3v2 = $fileDetails['tags']['id3v2'];
		
		//Before we compare the tag data that we fed to the tagging function
		//against the data that we just pulled off the newly-tagged file
		//(this ensures that all tags were written successfully), we must
		//first manipulate a handful of the key names.
		
		//When the tags are written, getID3 makes format-specific changes
		//to the key names that are provided as input. For this reason, when
		//reading the tag data back in for comparison, we have to account
		//for any key names that getID3 changed to suit the target tag format.
		
		//ID3v2 requires "comments" instead of "comment".
		
		//XXX TODO I had to comment-out these two lines to use getID3 1.9.3. These
		//lines don't cause an error in 1.7.9. It's a good thing, because I had
		//to revert back to 1.7.9 due to http://www.getid3.org/phpBB3/viewtopic.php?f=4&t=1379
		//-CBJ 2012.09.06.
		
		//UPDATE: Upgrading to getID3 1.9.9 required these lines to be commented-out
		//again; presumably, the author corrected the discrepency that made this
		//correction necessary in the first place. FWIW, I didn't test against
		//the bug that is referenced above, because I don't have a reproducible
		//sample on-hand. -CBJ 2015.01.08.
		
		#$tagData['comments'][0] = $tagData['comment'][0];
		#unset($tagData['comment']);
		
		//ID3v2 requires "track_number" instead of "tracknumber".
		$tagData['track_number'][0] = $tagData['tracknumber'][0];
		unset($tagData['tracknumber']);
		
		//We don't want to include artwork data when checking the success of
		//the other tag-writes.
		if (isset($tagData['attached_picture'])) {
			unset($tagData['attached_picture']);
		}
		
		$numWritesSucceeded = 0;
		
		//Now that any format-specific key names were changed back to
		//the generic forms that GetID3 expects, we'll compare each
		//tag on the file with the tag data that we attempted to apply
		//earlier, in order to determine whether or not each tag was
		//written successfully.
		foreach ($tagData as $fieldName => $fieldDataArray) {
			foreach ($fieldDataArray as $numericIndex => $fieldValue) {
				if ($id3v2[$fieldName][0] == $fieldValue) {
					$numWritesSucceeded++;
				}
			}
		}
		
		//We're able to compare how many tags were written versus how
		//many write attempts were made in order to determine our
		//success rate.
		
		if ($numWritesAttempted == $numWritesSucceeded) {
			return array('result' => TRUE, 'error' => NULL);
		}
		else {
			$error = 'The number of tag writes that succeeded (' . $numWritesSucceeded . ') is less than the number attempted (' . $numWritesAttempted . ')';
			return array('result' => FALSE, 'error' => $error);
		}
	}
}

//Adds tags to FLAC files. The $tagData input value should
//be an array that was generated using Music::generateGetid3Tag().
//The 'metaflac' binary must be available for this to work!
function tagFlacFile($file, $tagData, $allowBlank = FALSE, $coverFile = NULL)
{
	if (!file_exists($file)) {
		$error = 'The input file appears not to exist';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//Instantiate the getID3 object.
	$gid3 = new getID3;
	
	if (!is_object($gid3)) {
		$error = 'The getID3 object could not be instantiated';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//Attempt to acquire the audio file's properties.
	$fileDetails = $gid3->analyze($file);
	
	if (!is_array($fileDetails)) {
		$error = 'getID3\'s analyze() method did not return a usable array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//Ensure that the file on which we're attempting to operate is indeed
	//a FLAC file.
	//
	//Note: we could use the self::validateAudioFile() method, but there's
	//no reason to waste the time/memory required to call that function
	//when we have to call getID3's analyze() method again.
	if (!isset($fileDetails['fileformat']) || $fileDetails['fileformat'] != 'flac') {
		$error = 'The audio file does not validate as a FLAC file';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//A counter to store the number of tags that we attempted to write.
	$numWritesAttempted = 0;
	
	//Attempt to remove any existing tags before writing new tags.
	//IMPORTANT: The --remove-vc-all option is deprecated in favor of the
	//--remove-all-tags option; using the deprecated option will cause the
	//command to fail on systems on which the option is not supported.
	//Changed to --remove-all because cover art was not being removed. -CBJ 2011.01.18

    // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

    setlocale(LC_CTYPE, 'en_US.UTF-8');

	$cmd = 'metaflac --remove-all ' . escapeshellarg($file);

    // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

    $env = ['LC_ALL' => 'en_US.utf8'];

	$res = \GlobalMethods::openProcess($cmd, NULL, $env);
	
	//Attempt to acquire the audio file's properties, again, now that
	//we've attempted to remove any existing tags.
	$fileDetails = $gid3->analyze($file);
	
	if (!is_array($fileDetails)) {
		$error = 'getID3\'s analyze() method did not return a usable array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	//We attempted to remove all tags from the FLAC file; we can
	//determine whether or not we were successful in that effort by
	//checking to see if the vorbiscomment block is present in the file.
	
	if (isset($fileDetails['tags']['vorbiscomment'])) {
		$error = 'The vorbiscomment block was not removed for some reason';
		return array('result' => FALSE, 'error' => $error);
	}
	
	if (empty($tagData['date'][0]) || $tagData['date'][0] === 'Unknown') {
		unset($tagData['date']);
	}
	
	if (!empty($coverFile)) {
		$res = $this->embedFlacArt($coverFile, $file);
		
		if ($res['result'] !== FALSE) {
			
		}
		else {
			$error = 'Embedding cover art in FLAC file "' . $file . '" failed; ' . $res['error'];
			\GlobalMethods::logCriticalError($error);
		}
	}
	
	//Attempt to add each tag to the FLAC file, and keep track of the number
	//of write attempts. Given that the shell_exec() exit status is not a
	//reliable means by which to determine the success/failure of the
	//operation, we must compare the number of write attempts to the number
	//of tags that exist once we're done attempting to write.
	
	foreach ($tagData as $fieldName => $fieldDataArray) {
		foreach ($fieldDataArray as $numericIndex => $fieldValue) {
			// If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

			setlocale(LC_CTYPE, 'en_US.UTF-8');

			//IMPORTANT: The --set-vc-field option is deprecated in favor of the
			//--set-tag option; using the deprecated option will cause the command to
			//fail on systems on which the option is not supported.
			
			$cmd = 'metaflac --set-tag=' . escapeshellarg(ucfirst($fieldName)) . '=' . escapeshellarg($fieldValue) . ' ' . escapeshellarg($file);

			// If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

            $env = ['LC_ALL' => 'en_US.utf8'];

			$res = \GlobalMethods::openProcess($cmd, null, $env);

			$numWritesAttempted++;
		}
	}
	
	//Attempt to acquire the audio file's properties, again, now that we've
	//attempted to write new tags.
	$fileDetails = $gid3->analyze($file);
	
	$prefix = 'getID3\'s analyze() method';
	
	if (!is_array($fileDetails)) {
		$error = $prefix . ' did not return a usable array';
		return array('result' => FALSE, 'error' => $error);
	}
	
	if ($allowBlank !== TRUE) {
		if (!isset($fileDetails['tags']['vorbiscomment'])) {
			$error = $prefix . ' determined that the tags were not written for some reason';
			
			if (!empty($fileDetails['error'])) {
				for ($i = 0; $i < count($fileDetails['error']); $i++) {
					if ($i == 0) {
						$error .= ": ";
					}
					else {
						$error .= '; ';
					}
					
					$error .= $fileDetails['error'][$i];
				}
			}
			
			return array('result' => FALSE, 'error' => $error);
		}
		else {
			//If at least one tag was written, we'll end-up here.
			$vorbiscomment = $fileDetails['tags']['vorbiscomment'];
			
			$numWritesSucceeded = 0;
			
			//Now, we'll compare each tag on the file with the tag data that
			//we attempted to apply earlier, in order to determine whether
			//or not each tag was written successfully.
			foreach ($tagData as $fieldName => $fieldDataArray) {
				foreach ($fieldDataArray as $numericIndex => $fieldValue) {
					if ($vorbiscomment[$fieldName][0] == $fieldValue) {
						$numWritesSucceeded++;
					}
				}
			}
			
			//We're able to compare how many tags were written versus how
			//many write attempts were made in order to determine our
			//success rate.
			if ($numWritesAttempted == $numWritesSucceeded) {
				return array('result' => TRUE, 'error' => NULL);
			}
			else {
				$error = 'The number of tag writes that succeeded (' . $numWritesSucceeded . ') is less than the number attempted (' . $numWritesAttempted . ')';
				return array('result' => FALSE, 'error' => $error);
			}
		}
	}
	else {
		return array('result' => TRUE, 'error' => NULL);
	}
}

//Largely from the getID3 Write demo.
function prepareCoverImageForTag($imageFile)
{
	ob_start();
	
	if ($fd = fopen($imageFile, 'rb')) {
		ob_end_clean();
		
		$apicData = fread($fd, filesize($imageFile));
		
		fclose ($fd);

		list($APIC_width, $APIC_height, $apicImageTypeId) = getimagesize($imageFile);
		
		$imageTypes = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
		
		if (isset($imageTypes[$apicImageTypeId])) {
			return array('result' => $apicData, 'error' => NULL);
		}
		else {
			$error = 'Invalid image format (with APIC image type ID ' . $apicImageTypeId . ') (only GIF, JPEG, and PNG are supported)';
			return array('result' => FALSE, 'error' => $error);
		}
	}
	else {
		ob_end_clean();
		$error = 'Cannot open ' . $imageFile . ': ' . ob_get_contents();
		return array('result' => FALSE, 'error' => $error);
	}
}

function embedFlacArt($imageFile, $audioFile)
{
    // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

    setlocale(LC_CTYPE, 'en_US.UTF-8');

    $cmd = 'metaflac --import-picture-from=' . escapeshellarg($imageFile) . ' ' . escapeshellarg($audioFile);

    // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

    $env = ['LC_ALL' => 'en_US.utf8'];

	$res = \GlobalMethods::openProcess($cmd, NULL, $env);
	
	if ($res !== FALSE) {
		//As of this writing, metaflac returns an exit status of
		//zero (which cannot necessarily be relied upon on Windows)
		//and does not produce any output on success. The latter fact is
		//far more reliable than the exit status.
		
		if ($res['stdOut'] == '' && $res['stdErr'] == '') {
			return array('result' => TRUE, 'error' => NULL);
		}
		else {
			return array('result' => FALSE, 'error' => 'The call to `metaflac` produced output, which indicates an error condition: ' . \Utility::varToString($res));
		}
	}
	else {
		return array('result' => FALSE, 'error' => 'The process could not be opened: ' . $cmd);
	}
}

function removeArtwork($file)
{
    // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

    setlocale(LC_CTYPE, 'en_US.UTF-8');

    $cmd = 'metaflac --remove --block-type=PICTURE ' . escapeshellarg($file);

    // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

    $env = ['LC_ALL' => 'en_US.utf8'];

	$res = \GlobalMethods::openProcess($cmd, NULL, $env);
	
	if ($res !== FALSE) {
		//As of this writing, metaflac returns an exit status of
		//zero (which cannot necessarily be relied upon on Windows)
		//and does not produce any output on success. The latter fact is
		//far more reliable than the exit status.
		
		if ($res['stdOut'] == '' && $res['stdErr'] == '') {
			return array('result' => TRUE, 'error' => NULL);
		}
		else {
			return array('result' => FALSE, 'error' => 'The call to `metaflac` produced output, which indicates an error condition: ' . \Utility::varToString($res));
		}
	}
	else {
		return array('result' => FALSE, 'error' => 'The process could not be opened: ' . $cmd);
	}
}

}
