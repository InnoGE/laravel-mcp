<?php

namespace InnoGE\LaravelMcp\Types;

/**
 * InitializeResult
 *
 * Result of the initialize request.
 */
class InitializeResult
{
    /**
     * Protocol version
     */
    public string $protocolVersion = '2024-11-05'; // The MCP protocol version

    /**
     * Server capabilities
     */
    public array $capabilities;

    /**
     * Server info
     */
    public array $serverInfo;

    /**
     * Constructor
     *
     * @param  string  $name  Server name
     * @param  string  $version  Server version
     * @param  array  $capabilities  Server capabilities
     * @param  string  $protocolVersion  Optional protocol version
     */
    public function __construct(string $name, string $version, array $capabilities, string $protocolVersion = '2024-11-05')
    {
        $this->serverInfo = [
            'name' => $name,
            'version' => $version,
        ];
        $this->capabilities = $capabilities;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Create from an array
     *
     * @param  array  $data  The data to create from
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? 'unknown',
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
            'protocolVersion' => $this->protocolVersion,
            'serverInfo' => $this->serverInfo,
            'capabilities' => $this->capabilities,
        ];
    }
}
