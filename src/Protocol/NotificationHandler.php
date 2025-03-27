<?php

namespace InnoGE\LaravelMcp\Protocol;

/**
 * Interface NotificationHandler
 *
 * Defines the required methods for any notification handler in the MCP protocol.
 * Notification handlers process incoming notifications without returning a response.
 */
interface NotificationHandler
{
    /**
     * Handle a notification
     *
     * @param  string  $method  The method name from the notification
     * @param  array|null  $params  The parameters from the notification
     *
     * @throws \Exception If the notification cannot be handled
     */
    public function handleNotification(string $method, ?array $params = null): void;

    /**
     * Check if this handler can handle the given method
     *
     * @param  string  $method  The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool;
}
