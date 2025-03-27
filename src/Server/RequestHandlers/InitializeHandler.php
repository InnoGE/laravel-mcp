<?php

namespace InnoGE\LaravelMcp\Server\RequestHandlers;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Server\MCPServer;
use InnoGE\LaravelMcp\Types\InitializeParams;
use InnoGE\LaravelMcp\Utils\JsonRpcError;

/**
 * InitializeHandler
 *
 * Handler for the initialize request.
 */
class InitializeHandler implements RequestHandler
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
        return $method === 'initialize';
    }

    /**
     * Handle the initialize request
     *
     * @param  string  $method  The method name
     * @param  array|null  $params  The method parameters
     * @return array The response
     *
     * @throws JsonRpcError If initialization fails
     */
    public function handleRequest(string $method, ?array $params = null): array
    {
        if ($method !== 'initialize') {
            throw new JsonRpcError('Method not supported by this handler', JsonRpcError::METHOD_NOT_FOUND);
        }

        if (! is_array($params)) {
            throw new JsonRpcError('Invalid params for initialize', JsonRpcError::INVALID_PARAMS);
        }

        $initParams = InitializeParams::fromArray($params);
        $result = $this->server->initialize($initParams);

        return $result->toArray();
    }
}
