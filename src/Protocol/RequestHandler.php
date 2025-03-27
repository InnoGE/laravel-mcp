<?php

namespace InnoGE\LaravelMcp\Protocol;

/**
 * Interface RequestHandler
 *
 * Defines the required methods for any request handler in the MCP protocol.
 * Request handlers process incoming requests and return a response.
 */
interface RequestHandler
{
    /**
     * Handle a request and return a response
     *
     * @param  string  $method  The method name from the request
     * @param  array|null  $params  The parameters from the request
     * @return array The response data
     *
     * @throws \Exception If the request cannot be handled
     */
    public function handleRequest(string $method, ?array $params = null): array;

    /**
     * Check if this handler can handle the given method
     *
     * @param  string  $method  The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool;
}
