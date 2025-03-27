<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceSubscribeParams
 *
 * Parameters for the resources/subscribe request.
 */
class ResourceSubscribeParams
{
    /**
     * @var string The URI of the resource to subscribe to
     */
    public string $uri;

    /**
     * Create a new ResourceSubscribeParams instance
     *
     * @param string $uri The URI of the resource
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Convert the params to an array for JSON serialization
     *
     * @return array
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
     * @param array $data The data array
     * @return self
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['uri'])) {
            throw new \InvalidArgumentException('URI is required for resource subscribe params');
        }

        return new self($data['uri']);
    }
}
