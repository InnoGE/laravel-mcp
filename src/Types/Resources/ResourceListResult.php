<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceListResult
 *
 * Result for the resources/list request.
 */
class ResourceListResult
{
    /**
     * @var ResourceItem[] The list of resources
     */
    public array $resources;

    /**
     * @var string|null The cursor for the next page
     */
    public ?string $nextCursor;

    /**
     * Create a new ResourceListResult instance
     *
     * @param ResourceItem[] $resources The list of resources
     * @param string|null $nextCursor The cursor for the next page
     */
    public function __construct(array $resources, ?string $nextCursor = null)
    {
        $this->resources = $resources;
        $this->nextCursor = $nextCursor;
    }

    /**
     * Convert the result to an array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'resources' => array_map(fn(ResourceItem $item) => $item->toArray(), $this->resources),
        ];

        if ($this->nextCursor !== null) {
            $result['nextCursor'] = $this->nextCursor;
        }

        return $result;
    }
}
