<?php

namespace InnoGE\LaravelMcp\Resources;

use InnoGE\LaravelMcp\Types\Resources\ResourceContent;
use InnoGE\LaravelMcp\Types\Resources\ResourceItem;
use InnoGE\LaravelMcp\Types\Resources\ResourceTemplate;

/**
 * DefaultResourceProvider
 *
 * A basic implementation of the ResourceProviderInterface that manages resources in memory.
 */
class InMemoryResourceProvider implements ResourceProviderInterface
{
    /**
     * Collection of resources
     *
     * @var array<string, ResourceItem>
     */
    protected array $resources = [];

    /**
     * Collection of resource templates
     *
     * @var array<string, ResourceTemplate>
     */
    protected array $resourceTemplates = [];

    /**
     * Collection of resource contents
     *
     * @var array<string, ResourceContent>
     */
    protected array $resourceContents = [];

    /**
     * Add a resource to the provider
     *
     * @param  ResourceItem  $resource  The resource to add
     * @param  ResourceContent|null  $content  The resource content (optional)
     */
    public function addResource(ResourceItem $resource, ResourceContent $content): void
    {
        $this->resources[$resource->uri] = $resource;
        $this->resourceContents[$resource->uri] = $content;
    }

    /**
     * Add a resource template to the provider
     *
     * @param  ResourceTemplate  $template  The template to add
     */
    public function addResourceTemplate(ResourceTemplate $template): void
    {
        $this->resourceTemplates[$template->uriTemplate] = $template;
    }

    /**
     * List resources
     *
     * @param  string|null  $cursor  Pagination cursor
     * @param  int  $limit  Maximum number of items to return
     * @return array{items: ResourceItem[], nextCursor: ?string}
     */
    public function listResources(?string $cursor = null, int $limit = 100): array
    {
        // Simple implementation without pagination
        $items = array_values($this->resources);

        // Get resources after the cursor
        if ($cursor !== null) {
            $found = false;
            $filteredItems = [];

            foreach ($items as $item) {
                if ($found) {
                    $filteredItems[] = $item;
                } elseif ($item->uri === $cursor) {
                    $found = true;
                }
            }

            $items = $filteredItems;
        }

        // Limit the number of items
        $items = array_slice($items, 0, $limit);

        // Get the next cursor
        $nextCursor = null;
        if (count($items) === $limit && count($this->resources) > $limit) {
            $nextCursor = end($items)->uri;
        }

        return [
            'resources' => $items,
            'nextCursor' => $nextCursor,
        ];
    }

    /**
     * List resource templates
     *
     * @return ResourceTemplate[]
     */
    public function listResourceTemplates(): array
    {
        return array_values($this->resourceTemplates);
    }

    /**
     * Read a resource
     *
     * @param  string  $uri  The URI of the resource to read
     * @return ResourceContent[] The resource contents
     *
     * @throws \Exception If the resource cannot be read
     */
    public function readResource(string $uri): array
    {
        if (! isset($this->resourceContents[$uri])) {
            throw new \Exception("Resource not found: {$uri}");
        }

        return [$this->resourceContents[$uri]];
    }

    /**
     * Check if a resource exists
     *
     * @param  string  $uri  The URI of the resource
     * @return bool True if the resource exists
     */
    public function resourceExists(string $uri): bool
    {
        return isset($this->resources[$uri]);
    }
}
