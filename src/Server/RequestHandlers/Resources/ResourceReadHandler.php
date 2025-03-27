<?php

namespace InnoGE\LaravelMcp\Server\RequestHandlers\Resources;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Types\Resources\ResourceReadParams;
use InnoGE\LaravelMcp\Types\Resources\ResourceReadResult;
use InnoGE\LaravelMcp\Utils\JsonRpcError;

/**
 * ResourceReadHandler
 *
 * Handler for the resources/read request.
 */
class ResourceReadHandler implements RequestHandler
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
        return $method === 'resources/read';
    }

    /**
     * Handle the resources/read request
     *
     * @param  string  $method  The method name
     * @param  array|null  $params  The method parameters
     * @return array The response
     *
     * @throws JsonRpcError If the request fails
     */
    public function handleRequest(string $method, ?array $params = null): array
    {
        if ($method !== 'resources/read') {
            throw new JsonRpcError('Method not supported by this handler', JsonRpcError::METHOD_NOT_FOUND);
        }

        if (! is_array($params) || ! isset($params['uri'])) {
            throw new JsonRpcError('Missing required parameter: uri', JsonRpcError::INVALID_PARAMS);
        }

        try {
            $readParams = ResourceReadParams::fromArray($params);

            if (! $this->resourceProvider->resourceExists($readParams->uri)) {
                throw new JsonRpcError(
                    'Resource not found',
                    -32002,
                    ['uri' => $readParams->uri]
                );
            }

            $contents = $this->resourceProvider->readResource($readParams->uri);

            return (new ResourceReadResult($contents))->toArray();
        } catch (JsonRpcError $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new JsonRpcError(
                'Failed to read resource: '.$e->getMessage(),
                JsonRpcError::INTERNAL_ERROR
            );
        }
    }
}
