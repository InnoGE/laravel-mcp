<?php

namespace InnoGE\LaravelMcp\Server\RequestHandlers\Resources;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Server\MCPServer;
use InnoGE\LaravelMcp\Types\Resources\ResourceSubscribeParams;
use InnoGE\LaravelMcp\Utils\JsonRpcError;

/**
 * ResourceSubscribeHandler
 *
 * Handler for the resources/subscribe request.
 */
class ResourceSubscribeHandler implements RequestHandler
{
    /**
     * Resource provider
     */
    private ResourceProviderInterface $resourceProvider;

    /**
     * MCP server
     */
    private MCPServer $server;

    /**
     * Constructor
     *
     * @param ResourceProviderInterface $resourceProvider The resource provider
     * @param MCPServer $server The MCP server
     */
    public function __construct(ResourceProviderInterface $resourceProvider, MCPServer $server)
    {
        $this->resourceProvider = $resourceProvider;
        $this->server = $server;
    }

    /**
     * Check if this handler can handle the given method
     *
     * @param string $method The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool
    {
        return $method === 'resources/subscribe';
    }

    /**
     * Handle the resources/subscribe request
     *
     * @param string $method The method name
     * @param array|null $params The method parameters
     * @return array The response (empty object for subscriptions)
     *
     * @throws JsonRpcError If the request fails
     */
    public function handleRequest(string $method, ?array $params = null): array
    {
        if ($method !== 'resources/subscribe') {
            throw new JsonRpcError('Method not supported by this handler', JsonRpcError::METHOD_NOT_FOUND);
        }

        if (!is_array($params) || !isset($params['uri'])) {
            throw new JsonRpcError('Missing required parameter: uri', JsonRpcError::INVALID_PARAMS);
        }

        try {
            $subscribeParams = ResourceSubscribeParams::fromArray($params);

            // Check if the resource exists
            if (!$this->resourceProvider->resourceExists($subscribeParams->uri)) {
                throw new JsonRpcError(
                    'Resource not found',
                    -32002,
                    ['uri' => $subscribeParams->uri]
                );
            }

            // Register the subscription (server would track this)
            $this->server->addResourceSubscription($subscribeParams->uri);
            
            // Return an empty object as success response
            return [];
        } catch (JsonRpcError $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new JsonRpcError(
                'Failed to subscribe to resource: ' . $e->getMessage(),
                JsonRpcError::INTERNAL_ERROR
            );
        }
    }
}
