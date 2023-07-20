<?php

/** 
 * Logger
 * @category description 
 * @author armin malekzadeh <arixologist@gmail.com> 
 * @version 1.0 
 */ 

namespace HttpClient;

class Logger
{
    private string $logFile;

    public function __construct(string $logFile = 'HttpClient.log')
    {
        $this->logFile = $logFile;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        $formattedMessage = sprintf(
            "[%s] [%s]: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );

        // Log to file
        error_log($formattedMessage, 3, $this->logFile);

        // Display log in the output
        echo $formattedMessage;
    }
}