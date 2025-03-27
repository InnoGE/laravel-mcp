<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceReadResult
 *
 * Result for the resources/read request.
 */
class ResourceReadResult
{
    /**
     * @var ResourceContent[] The resource contents
     */
    public array $contents;

    /**
     * Create a new ResourceReadResult instance
     *
     * @param ResourceContent[] $contents The resource contents
     */
    public function __construct(array $contents)
    {
        $this->contents = $contents;
    }

    /**
     * Convert the result to an array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'contents' => array_map(fn(ResourceContent $content) => $content->toArray(), $this->contents),
        ];
    }
}
