<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceItem
 *
 * Represents a resource in the Model Context Protocol.
 */
class ResourceItem
{
    /**
     * @var string The URI of the resource
     */
    public string $uri;

    /**
     * @var string The name of the resource
     */
    public string $name;

    /**
     * @var string|null The description of the resource
     */
    public ?string $description;

    /**
     * @var string|null The MIME type of the resource
     */
    public ?string $mimeType;

    /**
     * @var int|null The size of the resource in bytes
     */
    public ?int $size;

    /**
     * Create a new ResourceItem instance
     *
     * @param  string  $uri  The URI of the resource
     * @param  string  $name  The name of the resource
     * @param  string|null  $description  The description of the resource
     * @param  string|null  $mimeType  The MIME type of the resource
     * @param  int|null  $size  The size of the resource in bytes
     */
    public function __construct(
        string $uri,
        string $name,
        ?string $description = null,
        ?string $mimeType = null,
        ?int $size = null
    ) {
        $this->uri = $uri;
        $this->name = $name;
        $this->description = $description;
        $this->mimeType = $mimeType;
        $this->size = $size;
    }

    /**
     * Convert the resource item to an array for JSON serialization
     */
    public function toArray(): array
    {
        $result = [
            'uri' => $this->uri,
            'name' => $this->name,
        ];

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }

        if ($this->mimeType !== null) {
            $result['mimeType'] = $this->mimeType;
        }

        if ($this->size !== null) {
            $result['size'] = $this->size;
        }

        return $result;
    }

    /**
     * Create a resource item from an array
     *
     * @param  array  $data  The data array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uri'],
            $data['name'],
            $data['description'] ?? null,
            $data['mimeType'] ?? null,
            $data['size'] ?? null
        );
    }
}
