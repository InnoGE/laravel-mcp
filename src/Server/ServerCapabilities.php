<?php

namespace InnoGE\LaravelMcp\Server;

/**
 * ServerCapabilities
 *
 * Defines the capabilities of an MCP server.
 * These capabilities are communicated to the client during initialization.
 */
class ServerCapabilities
{
    /**
     * Whether the server supports resources
     */
    private bool $supportsResources = false;

    /**
     * Resource capabilities configuration
     */
    private ?array $resourcesConfig = null;

    /**
     * Whether the server supports tools
     */
    private bool $supportsTools = false;

    /**
     * Tool capabilities configuration
     */
    private ?array $toolsConfig = null;

    /**
     * Whether the server supports prompts
     */
    private bool $supportsPrompts = false;

    /**
     * Prompt capabilities configuration
     */
    private ?array $promptsConfig = null;

    /**
     * Whether the server supports sampling
     */
    private bool $supportsSampling = false;

    /**
     * Sampling capabilities configuration
     */
    private ?array $samplingConfig = null;

    /**
     * Enable resources capability
     *
     * @param  bool  $subscribeSupport  Whether to support resource subscriptions
     * @param  bool  $listChangedSupport  Whether to support list_changed notifications
     * @return $this
     */
    public function withResources(bool $subscribeSupport = false, bool $listChangedSupport = false): self
    {
        $this->supportsResources = true;
        $this->resourcesConfig = [];
        
        if ($subscribeSupport) {
            $this->resourcesConfig['subscribe'] = true;
        }
        
        if ($listChangedSupport) {
            $this->resourcesConfig['listChanged'] = true;
        }

        return $this;
    }

    /**
     * Enable tools capability
     *
     * @param  array|null  $config  Optional configuration for tools
     * @return $this
     */
    public function withTools(?array $config = []): self
    {
        $this->supportsTools = true;
        $this->toolsConfig = $config;

        return $this;
    }

    /**
     * Enable prompts capability
     *
     * @param  array|null  $config  Optional configuration for prompts
     * @return $this
     */
    public function withPrompts(?array $config = []): self
    {
        $this->supportsPrompts = true;
        $this->promptsConfig = $config;

        return $this;
    }

    /**
     * Enable sampling capability
     *
     * @param  array|null  $config  Optional configuration for sampling
     * @return $this
     */
    public function withSampling(?array $config = []): self
    {
        $this->supportsSampling = true;
        $this->samplingConfig = $config;

        return $this;
    }

    /**
     * Convert the capabilities to an array for JSON serialization
     */
    public function toArray(): array
    {
        $capabilities = [];

        if ($this->supportsResources) {
            $capabilities['resources'] = $this->resourcesConfig ?? new \stdClass;
        }

        if ($this->supportsTools) {
            $capabilities['tools'] = $this->toolsConfig ?? new \stdClass;
        }

        if ($this->supportsPrompts) {
            $capabilities['prompts'] = $this->promptsConfig ?? new \stdClass;
        }

        if ($this->supportsSampling) {
            $capabilities['sampling'] = $this->samplingConfig ?? new \stdClass;
        }

        return $capabilities;
    }
}
