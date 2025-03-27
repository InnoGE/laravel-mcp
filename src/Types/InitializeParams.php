<?php

namespace InnoGE\LaravelMcp\Types;

/**
 * InitializeParams
 *
 * Parameters for the initialize request.
 */
class InitializeParams
{
    /**
     * Protocol version
     */
    public string $version;

    /**
     * Client capabilities
     */
    public array $capabilities;

    /**
     * Constructor
     *
     * @param  string  $version  Protocol version
     * @param  array  $capabilities  Client capabilities
     */
    public function __construct(string $version, array $capabilities)
    {
        $this->version = $version;
        $this->capabilities = $capabilities;
    }

    /**
     * Create from an array
     *
     * @param  array  $data  The data to create from
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['version'] ?? '1.0',
            $data['capabilities'] ?? []
        );
    }

    /**
     * Convert to an array
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'capabilities' => $this->capabilities,
        ];
    }
}
