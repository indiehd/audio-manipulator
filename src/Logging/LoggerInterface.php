<?php

namespace IndieHD\AudioManipulator\Logging;

interface LoggerInterface
{
    public function configureLogger(string $logName): void;

    public function enableLogging(): void;

    public function disableLogging(): void;

    public function log(string $message, string $level = 'info'): void;
}
