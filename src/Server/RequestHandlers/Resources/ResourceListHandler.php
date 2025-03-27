<?php

namespace InnoGE\LaravelMcp\Server\RequestHandlers\Resources;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Types\Resources\ResourceListParams;
use InnoGE\LaravelMcp\Types\Resources\ResourceListResult;
use InnoGE\LaravelMcp\Utils\JsonRpcError;

/**
 * ResourceListHandler
 *
 * Handler for the resources/list request.
 */
class ResourceListHandler implements RequestHandler
{
    /**
     * Resource provider
     */
    private ResourceProviderInterface $resourceProvider;

    /**
     * Constructor
     *
     * @param  ResourceProviderInterface  $resourceProvider  The resource provider
     */
    public function __construct(ResourceProviderInterface $resourceProvider)
    {
        $this->resourceProvider = $resourceProvider;
    }

    /**
     * Check if this handler can handle the given method
     *
     * @param  string  $method  The method name to check
     * @return bool True if this handler can handle the method
     */
    public function canHandle(string $method): bool
    {
        return $method === 'resources/list';
    }

    /**
     * Handle the resources/list request
     *
     * @param  string  $method  The method name
     * @param  array|null  $params  The method parameters
     * @return array The response
     *
     * @throws JsonRpcError If the request fails
     */
    public function handleRequest(string $method, ?array $params = null): array
    {
        if ($method !== 'resources/list') {
            throw new JsonRpcError('Method not supported by this handler', JsonRpcError::METHOD_NOT_FOUND);
        }

        try {
            $listParams = new ResourceListParams(
                $params['cursor'] ?? null
            );

            $result = $this->resourceProvider->listResources($listParams->cursor);

            return (new ResourceListResult(
                $result['resources'],
                $result['nextCursor'] ?? null
            ))->toArray();
        } catch (\Exception $e) {
            throw new JsonRpcError(
                'Failed to list resources: '.$e->getMessage(),
                JsonRpcError::INTERNAL_ERROR
            );
        }
    }
}
