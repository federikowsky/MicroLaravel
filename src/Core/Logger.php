<?php

namespace App\Core;

class Logger {
    protected $logFile;
    protected $logLevel;

    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;

    protected $logLevels = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
    ];

    public function __construct($logFile, $logLevel = self::DEBUG) {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
    }

    private function log($message, $level = self::INFO) {
        if ($level >= $this->logLevel) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] [{$this->logLevels[$level]}] $message" . PHP_EOL;
            file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        }
    }

    public function debug($message) {
        $this->log($message, self::DEBUG);
    }

    public function info($message) {
        $this->log($message, self::INFO);
    }

    public function warning($message) {
        $this->log($message, self::WARNING);
    }

    public function error($message) {
        $this->log($message, self::ERROR);
    }
}