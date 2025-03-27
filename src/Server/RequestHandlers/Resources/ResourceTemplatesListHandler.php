<?php

namespace InnoGE\LaravelMcp\Server\RequestHandlers\Resources;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Types\Resources\ResourceTemplatesListResult;
use InnoGE\LaravelMcp\Utils\JsonRpcError;

/**
 * ResourceTemplatesListHandler
 *
 * Handler for the resources/templates/list request.
 */
class ResourceTemplatesListHandler implements RequestHandler
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
        return $method === 'resources/templates/list';
    }

    /**
     * Handle the resources/templates/list request
     *
     * @param  string  $method  The method name
     * @param  array|null  $params  The method parameters
     * @return array The response
     *
     * @throws JsonRpcError If the request fails
     */
    public function handleRequest(string $method, ?array $params = null): array
    {
        if ($method !== 'resources/templates/list') {
            throw new JsonRpcError('Method not supported by this handler', JsonRpcError::METHOD_NOT_FOUND);
        }

        try {
            $templates = $this->resourceProvider->listResourceTemplates();

            return (new ResourceTemplatesListResult($templates))->toArray();
        } catch (\Exception $e) {
            throw new JsonRpcError(
                'Failed to list resource templates: '.$e->getMessage(),
                JsonRpcError::INTERNAL_ERROR
            );
        }
    }
}
