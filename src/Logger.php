<?php

namespace Ariyx;

/**
 * Logger class for handling logs.
 * 
 * @category Logging
 * @package Ariyx
 * @author Armin Malekzadeh <arixologist@gmail.com>
 * @version 1.1
 */
class Logger
{
    private readonly string $logFile;

    public function __construct(string $logFile = 'HttpClient.log')
    {
        $this->logFile = $logFile;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        $formattedMessage = $this->formatMessage($message, $level);
        $this->writeLog($formattedMessage);
    }

    public function info(string $message): void
    {
        $this->log($message, 'INFO');
    }

    public function warning(string $message): void
    {
        $this->log($message, 'WARNING');
    }

    public function error(string $message): void
    {
        $this->log($message, 'ERROR');
    }

    private function formatMessage(string $message, string $level): string
    {
        return sprintf(
            "[%s] [%s]: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );
    }

    private function writeLog(string $formattedMessage): void
    {
        error_log($formattedMessage, 3, $this->logFile);
        echo $formattedMessage;
    }
}