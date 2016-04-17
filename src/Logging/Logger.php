<?php namespace IndieTorrent\AudioManipulator\Logging;

use IndieTorrent\AudioManipulator\Logging\CommandHistory;

class Logger
{

public function __construct(
	CommandHistory $commandHistory
)
{
	$this->commandHistory = $commandHistory;
}

}
