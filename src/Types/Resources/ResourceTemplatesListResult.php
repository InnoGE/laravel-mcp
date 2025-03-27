<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceTemplatesListResult
 *
 * Result for the resources/templates/list request.
 */
class ResourceTemplatesListResult
{
    /**
     * @var ResourceTemplate[] The list of resource templates
     */
    public array $resourceTemplates;

    /**
     * Create a new ResourceTemplatesListResult instance
     *
     * @param ResourceTemplate[] $resourceTemplates The list of resource templates
     */
    public function __construct(array $resourceTemplates)
    {
        $this->resourceTemplates = $resourceTemplates;
    }

    /**
     * Convert the result to an array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'resourceTemplates' => array_map(fn(ResourceTemplate $template) => $template->toArray(), $this->resourceTemplates),
        ];
    }
}
