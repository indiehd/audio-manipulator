<?php namespace IndieTorrent\AudioManipulator;

use IndieTorrent\AudioManipulator\Logging\Logger;
use IndieTorrent\AudioManipulator\Transcoding\Transcoder;
use IndieTorrent\AudioManipulator\Tagging\Tagger;
use IndieTorrent\AudioManipulator\Exceptions\AudioManipulatorException;

class AudioManipulator
{

public $singleThreaded = TRUE;

function __construct(
	Logger $logger,
	Transcoder $transcoder,
	Tagger $tagger
)
{
	$this->logger = $logger;
	$this->transcoder = $transcoder;
	$this->tagger = $tagger;
	
	$safeModeStatus = ini_get('safe_mode');
	
	if ($safeModeStatus == 'on' || $safeModeStatus == '1') {
		throw new AudioManipulatorException('Audio cannot be manipulated when Safe Mode is enabled because the required shell_exec() function is disabled');
	}
}

}
