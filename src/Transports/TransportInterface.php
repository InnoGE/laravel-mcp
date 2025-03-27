<?php

namespace InnoGE\LaravelMcp\Transports;

/**
 * Interface TransportInterface
 *
 * Defines the required methods for any transport implementation in the MCP protocol.
 * Transports handle the raw communication between client and server.
 */
interface TransportInterface
{
    /**
     * Connect to the transport
     */
    public function connect(): void;

    /**
     * Disconnect from the transport
     */
    public function disconnect(): void;

    /**
     * Send a message through the transport
     *
     * @param  array  $message  The message to send (will be JSON-encoded)
     */
    public function send(array $message): void;

    /**
     * Register a handler for incoming messages
     *
     * @param  callable  $handler  Function to call when a message is received
     */
    public function onMessage(callable $handler): void;

    /**
     * Check if the transport is connected
     */
    public function isConnected(): bool;
}
