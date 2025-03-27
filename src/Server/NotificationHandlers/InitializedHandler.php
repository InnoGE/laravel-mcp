<?php

namespace InnoGE\LaravelMcp\Server\NotificationHandlers;

use InnoGE\LaravelMcp\Protocol\NotificationHandler;
use InnoGE\LaravelMcp\Server\MCPServer;

/**
 * InitializedHandler
 *
 * Handler for the initialized notification.
 * This notification is sent by the client after initialization is complete.
 */
class InitializedHandler implements NotificationHandler
{
    /**
     * MCP server instance
     */
    private MCPServer $server;

    /**
     * Constructor
     *
     * @param  MCPServer  $server  The server instance
     */
    public function __construct(MCPServer $server)
    {
        $this->server = $server;
    }

    /**
     * Check if this handler can handle the given method
     *
     * @param  string  $method  The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool
    {
        return $method === 'initialized';
    }

    /**
     * Handle the initialized notification
     *
     * @param  string  $method  The method name
     * @param  array|null  $params  The method parameters
     */
    public function handleNotification(string $method, ?array $params = null): void
    {
        if (function_exists('info')) {
            info('MCP client initialization completed');
        }
    }
}
