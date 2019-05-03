<?php

namespace IndieHD\AudioManipulator\Logging;

interface LoggerInterface
{
    public function configureLogger(string $logName): void;

    public function log(string $message, string $level = 'info'): void;
}
