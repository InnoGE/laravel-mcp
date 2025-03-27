<?php

namespace InnoGE\LaravelMcp\Resources;

use InnoGE\LaravelMcp\Types\Resources\ResourceContent;
use InnoGE\LaravelMcp\Types\Resources\ResourceItem;
use InnoGE\LaravelMcp\Types\Resources\ResourceTemplate;

/**
 * ResourceProviderInterface
 *
 * Interface for providers that supply resources to the MCP server.
 */
interface ResourceProviderInterface
{
    /**
     * List available resources
     *
     * @param  string|null  $cursor  Pagination cursor
     * @return array Array with 'resources' (ResourceItem[]) and 'nextCursor' (string|null)
     */
    public function listResources(?string $cursor = null): array;

    /**
     * Read a resource
     *
     * @param  string  $uri  The URI of the resource to read
     * @return ResourceContent[] The resource contents
     *
     * @throws \Exception If the resource cannot be read
     */
    public function readResource(string $uri): array;

    /**
     * List resource templates
     *
     * @return ResourceTemplate[] The list of resource templates
     */
    public function listResourceTemplates(): array;

    /**
     * Check if a resource exists
     *
     * @param  string  $uri  The URI to check
     * @return bool True if the resource exists
     */
    public function resourceExists(string $uri): bool;
}
