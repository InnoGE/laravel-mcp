<?php

namespace InnoGE\LaravelMcp\Transports;

use RuntimeException;

/**
 * StdioTransport
 *
 * Implementation of the Transport interface that uses PHP's standard input/output
 * streams for communication. This is particularly useful for CLI applications
 * and for local process communication.
 */
class StdioTransport implements TransportInterface
{
    /**
     * Standard input resource handle
     *
     * @var resource|null
     */
    private $stdin;

    /**
     * Standard output resource handle
     *
     * @var resource|null
     */
    private $stdout;

    /**
     * Flag indicating if the transport is connected
     */
    private bool $connected = false;

    /**
     * Array of message handler callbacks
     *
     * @var callable[]
     */
    private array $messageHandlers = [];

    /**
     * Flag to control the input processing loop
     */
    private bool $continueReading = true;

    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * Connect to STDIO streams
     *
     * @throws RuntimeException If the streams cannot be opened
     */
    public function connect(): void
    {
        if ($this->connected) {
            return;
        }

        $this->stdin = fopen('php://stdin', 'r');
        if ($this->stdin === false) {
            throw new RuntimeException('Failed to open stdin');
        }

        $this->stdout = fopen('php://stdout', 'w');
        if ($this->stdout === false) {
            throw new RuntimeException('Failed to open stdout');
        }

        // Set the input stream to non-blocking mode
        stream_set_blocking($this->stdin, false);

        $this->connected = true;
    }

    /**
     * Disconnect from STDIO streams
     */
    public function disconnect(): void
    {
        $this->continueReading = false;

        if ($this->stdin && is_resource($this->stdin)) {
            fclose($this->stdin);
            $this->stdin = null;
        }

        if ($this->stdout && is_resource($this->stdout)) {
            fclose($this->stdout);
            $this->stdout = null;
        }

        $this->connected = false;
    }

    /**
     * Send a message through stdout
     *
     * @param  array  $message  The message to send
     *
     * @throws RuntimeException If the transport is not connected
     */
    public function send(array $message): void
    {
        if (! $this->connected || ! $this->stdout) {
            throw new RuntimeException('Transport not connected');
        }

        // Encode the message with JSON and add a newline
        $jsonMessage = json_encode($message)."\n";

        fwrite($this->stdout, $jsonMessage);
        fflush($this->stdout);
    }

    /**
     * Register a handler for incoming messages
     *
     * @param  callable  $handler  Function to call when a message is received
     */
    public function onMessage(callable $handler): void
    {
        $this->messageHandlers[] = $handler;
    }

    /**
     * Check if the transport is connected
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Process input from stdin
     * This method should be called in a loop or separate process
     *
     * @param  int  $timeout  Timeout in microseconds between read attempts
     *
     * @throws RuntimeException If the transport is not connected
     */
    public function processInput(int $timeout = 10000): void
    {
        if (! $this->connected || ! $this->stdin) {
            throw new RuntimeException('Transport not connected');
        }

        // Read a line from stdin
        $line = fgets($this->stdin);

        // If we got a line, process it
        if ($line !== false) {
            $this->processLine($line);
        }

        // Sleep for a bit to prevent high CPU usage when there's no input
        usleep($timeout);
    }

    /**
     * Start the input processing loop
     * This will block the current thread until disconnect() is called
     */
    public function startReadLoop(): void
    {
        $this->continueReading = true;

        while ($this->continueReading && $this->connected) {
            $this->processInput();
        }
    }

    /**
     * Process a line of input
     *
     * @param  string  $line  The line to process
     */
    private function processLine(string $line): void
    {
        // Try to decode the JSON
        $message = json_decode($line, true);

        // If we got valid JSON, call the message handlers
        if (is_array($message)) {
            foreach ($this->messageHandlers as $handler) {
                $handler($message);
            }
        }
    }
}
