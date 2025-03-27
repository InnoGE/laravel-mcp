<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceContent
 *
 * Represents the content of a resource in the Model Context Protocol.
 */
class ResourceContent
{
    /**
     * @var string The URI of the resource
     */
    public string $uri;

    /**
     * @var string|null The MIME type of the resource
     */
    public ?string $mimeType;

    /**
     * @var string|null The text content of the resource
     */
    public ?string $text;

    /**
     * @var string|null The base64-encoded binary content of the resource
     */
    public ?string $blob;

    /**
     * Create a new ResourceContent instance
     *
     * @param string $uri The URI of the resource
     * @param string|null $mimeType The MIME type of the resource
     * @param string|null $text The text content of the resource
     * @param string|null $blob The base64-encoded binary content of the resource
     */
    public function __construct(
        string $uri,
        ?string $mimeType = null,
        ?string $text = null,
        ?string $blob = null
    ) {
        $this->uri = $uri;
        $this->mimeType = $mimeType;
        $this->text = $text;
        $this->blob = $blob;
    }

    /**
     * Create a text content resource
     *
     * @param string $uri The URI of the resource
     * @param string $text The text content
     * @param string|null $mimeType The MIME type (defaults to text/plain)
     * @return self
     */
    public static function text(string $uri, string $text, ?string $mimeType = 'text/plain'): self
    {
        return new self($uri, $mimeType, $text, null);
    }

    /**
     * Create a binary content resource
     *
     * @param string $uri The URI of the resource
     * @param string $blob The base64-encoded binary content
     * @param string|null $mimeType The MIME type (defaults to application/octet-stream)
     * @return self
     */
    public static function binary(string $uri, string $blob, ?string $mimeType = 'application/octet-stream'): self
    {
        return new self($uri, $mimeType, null, $blob);
    }

    /**
     * Convert the resource content to an array for JSON serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'uri' => $this->uri,
        ];

        if ($this->mimeType !== null) {
            $result['mimeType'] = $this->mimeType;
        }

        if ($this->text !== null) {
            $result['text'] = $this->text;
        }

        if ($this->blob !== null) {
            $result['blob'] = $this->blob;
        }

        return $result;
    }

    /**
     * Create a resource content from an array
     *
     * @param array $data The data array
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uri'],
            $data['mimeType'] ?? null,
            $data['text'] ?? null,
            $data['blob'] ?? null
        );
    }
}
