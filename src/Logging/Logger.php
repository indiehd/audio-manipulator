<?php namespace

IndieHD\AudioManipulator\Logging;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Monolog\Handler\HandlerInterface;

class Logger implements LoggerInterface
{
    private $logger;
    private $loggingEnabled = false;

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

        if (getenv('ENABLE_LOGGING') === 'true') {
            $this->loggingEnabled = true;
        }
    }

    public function log(string $message, string $level = 'info'): void
    {
        if ($this->loggingEnabled) {
            $this->logger->{$level}($message);
        }
    }
}
