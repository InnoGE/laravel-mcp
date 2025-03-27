<?php

namespace InnoGE\LaravelMcp\Logger;

use Illuminate\Console\Command;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Command logger that outputs to STDERR
 */
class CommandLogger extends AbstractLogger
{
    /**
     * The command instance
     */
    protected Command $command;

    /**
     * Whether debug is enabled
     */
    protected bool $debugEnabled;

    /**
     * Constructor
     */
    public function __construct(Command $command, bool $debugEnabled = false)
    {
        $this->command = $command;
        $this->debugEnabled = $debugEnabled;
    }

    /**
     * Log a message
     *
     * @param  mixed  $level
     * @param  string  $message
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        // Skip debug messages if debug is not enabled
        if ($level === LogLevel::DEBUG && ! $this->debugEnabled) {
            return;
        }

        $prefix = strtoupper($level);
        fwrite(STDERR, "[{$prefix}] {$message}\n");
    }
}
