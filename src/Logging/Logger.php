<?php

namespace

IndieHD\AudioManipulator\Logging;

use Monolog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class Logger implements LoggerInterface
{
    protected $logger;
    protected $loggingEnabled = false;

    public function __construct(
        PsrLoggerInterface $logger,
        HandlerInterface $handler
    ) {
        $this->logger = $logger;
        $this->handler = $handler;
    }

    public function configureLogger(string $logName): void
    {
        if (!empty(getenv($logName))) {
            $this->logger->pushHandler($this->handler);
        }

        if (getenv('ENABLE_LOGGING') == true) {
            $this->enableLogging();
        }
    }

    public function enableLogging(): void
    {
        $this->loggingEnabled = true;
    }

    public function disableLogging(): void
    {
        $this->loggingEnabled = false;
    }

    public function log(string $message, string $level = 'info'): void
    {
        if ($this->loggingEnabled) {
            $this->logger->{$level}($message);
        }
    }
}
