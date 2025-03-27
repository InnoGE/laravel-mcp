<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceReadParams
 *
 * Parameters for the resources/read request.
 */
class ResourceReadParams
{
    /**
     * @var string The URI of the resource to read
     */
    public string $uri;

    /**
     * Create a new ResourceReadParams instance
     *
     * @param  string  $uri  The URI of the resource
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Convert the params to an array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'uri' => $this->uri,
        ];
    }

    /**
     * Create params from an array
     *
     * @param  array  $data  The data array
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['uri'])) {
            throw new \InvalidArgumentException('URI is required for resource read params');
        }

        return new self($data['uri']);
    }
}
