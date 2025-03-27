<?php

namespace InnoGE\LaravelMcp\Types\Resources;

/**
 * ResourceListParams
 *
 * Parameters for the resources/list request.
 */
class ResourceListParams
{
    /**
     * @var string|null The cursor for pagination
     */
    public ?string $cursor;

    /**
     * Create a new ResourceListParams instance
     *
     * @param  string|null  $cursor  The cursor for pagination
     */
    public function __construct(?string $cursor = null)
    {
        $this->cursor = $cursor;
    }

    /**
     * Convert the params to an array for JSON serialization
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->cursor !== null) {
            $result['cursor'] = $this->cursor;
        }

        return $result;
    }

    /**
     * Create params from an array
     *
     * @param  array  $data  The data array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['cursor'] ?? null
        );
    }
}
