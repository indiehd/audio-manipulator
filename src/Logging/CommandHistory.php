<?php namespace IndieTorrent\AudioManipulator\Logging;

class CommandHistory
{

/**
 * An array in which to store a history of shell commands.
 * This makes troubleshooting failed operations much easier.
 * @var array
 */
public $commandHistory = [];

public function __construct()
{
	
}

}
