<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceTemplate
 *
 * Represents a resource template in the Model Context Protocol.
 */
class ResourceTemplate
{
    /**
     * @var string The URI template of the resource
     */
    public string $uriTemplate;

    /**
     * @var string The name of the resource template
     */
    public string $name;

    /**
     * @var string|null The description of the resource template
     */
    public ?string $description;

    /**
     * @var string|null The MIME type of the resource
     */
    public ?string $mimeType;

    /**
     * Create a new ResourceTemplate instance
     *
     * @param string $uriTemplate The URI template of the resource
     * @param string $name The name of the resource template
     * @param string|null $description The description of the resource template
     * @param string|null $mimeType The MIME type of the resource
     */
    public function __construct(
        string $uriTemplate,
        string $name,
        ?string $description = null,
        ?string $mimeType = null
    ) {
        $this->uriTemplate = $uriTemplate;
        $this->name = $name;
        $this->description = $description;
        $this->mimeType = $mimeType;
    }

    /**
     * Convert the resource template to an array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'uriTemplate' => $this->uriTemplate,
            'name' => $this->name,
        ];

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }

        if ($this->mimeType !== null) {
            $result['mimeType'] = $this->mimeType;
        }

        return $result;
    }

    /**
     * Create a resource template from an array
     *
     * @param array $data The data array
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uriTemplate'],
            $data['name'],
            $data['description'] ?? null,
            $data['mimeType'] ?? null
        );
    }
}
