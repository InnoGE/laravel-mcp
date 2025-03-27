<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceUnsubscribeParams
 *
 * Parameters for the resources/unsubscribe request.
 */
class ResourceUnsubscribeParams
{
    /**
     * @var string The URI of the resource to unsubscribe from
     */
    public string $uri;

    /**
     * Create a new ResourceUnsubscribeParams instance
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
            throw new \InvalidArgumentException('URI is required for resource unsubscribe params');
        }

        return new self($data['uri']);
    }
}
