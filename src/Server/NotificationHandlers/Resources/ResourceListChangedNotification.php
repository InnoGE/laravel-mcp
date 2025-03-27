<?php

namespace InnoGE\LaravelMcp\Server\NotificationHandlers\Resources;

use InnoGE\LaravelMcp\Protocol\MCPProtocol;
use InnoGE\LaravelMcp\Protocol\NotificationHandler;

/**
 * ResourceListChangedNotification
 *
 * Sends a notification when the list of available resources has changed.
 */
class ResourceListChangedNotification implements NotificationHandler
{
    /**
     * The protocol to use for sending notifications
     */
    private MCPProtocol $protocol;

    /**
     * Constructor
     *
     * @param  MCPProtocol  $protocol  The protocol to use for sending notifications
     */
    public function __construct(MCPProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Send a notification that the resource list has changed
     */
    public function notify(): void
    {
        $this->protocol->sendNotification('notifications/resources/list_changed');
    }

    /**
     * Handle a notification
     *
     * @param  string  $method  The method name from the notification
     * @param  array|null  $params  The parameters from the notification
     *
     * @throws \Exception If the notification cannot be handled
     */
    public function handleNotification(string $method, ?array $params = null): void
    {
        // This is an outgoing notification only, we don't handle incoming notifications
        throw new \Exception('This handler only sends outgoing notifications, not handles incoming ones');
    }

    /**
     * Check if this handler can handle the given method
     *
     * @param  string  $method  The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool
    {
        return $method === 'notifications/resources/list_changed';
    }
}
