<?php namespace Indietorrent\AudioManipulator\Validation;

class Validator
{

//Accepts an audio file as input and ensures that the file is of the
//type specified.
function validateAudioFile($file, $type)
{
	if (!file_exists($file)) {
		trigger_error('The input file appears not to exist', E_USER_WARNING);
		return FALSE;	
	}
	
	if ($type != 'wav' && $type != 'flac' && $type != 'mp3') {
		trigger_error('A valid audio file type was not specified', E_USER_WARNING);
		return FALSE;
	}
	
	$gid3 = new \getID3;
	
	if (!is_object($gid3)) {
		trigger_error('The getID3 object could not be instantiated', E_USER_WARNING);
		return FALSE;
	}
	
	$fileDetails = $gid3->analyze($file);
	if (!is_array($fileDetails)) {
		trigger_error('getID3\'s analyze() method did not return a usable array', E_USER_WARNING);
		return FALSE;
	}
	
	if ($type == 'wav') {
		//When certain FLAC files are converted to WAV files, the dataformat may
		//be "mp1" or "mp2" instead of "wav".
		//Both data formats are lossless, and therefore acceptable.
		
		$acceptableWavFormats = array(
			'wav',
			'mp1',
			'mp2',
		);
		
		if (!isset($fileDetails['audio']['dataformat']) || !in_array($fileDetails['audio']['dataformat'], $acceptableWavFormats)) {
			trigger_error('The audio file\'s ("' . $file . '") data format could not be ascertained, or the format was not within the acceptable list of WAV audio data formats (format was "' . $fileDetails['audio']['dataformat'] . '")', E_USER_WARNING);
			return FALSE;
		}
	}
	elseif ($type == 'flac') {
		if (!isset($fileDetails['fileformat']) || $fileDetails['fileformat'] != 'flac') {
			#trigger_error('The audio file\'s file format could not be ascertained', E_USER_WARNING);
			return FALSE;
		}
	}
	elseif ($type == 'mp3') {
		if (!isset($fileDetails['fileformat']) || $fileDetails['fileformat'] != 'mp3') {
			#trigger_error('The audio file\'s file format could not be ascertained', E_USER_WARNING);
			return FALSE;
		}
	}
	
	return $fileDetails;
}

}
