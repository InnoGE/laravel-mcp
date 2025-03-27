<?php

namespace InnoGE\LaravelMcp\Tools;

use Illuminate\Container\Container;

/**
 * Registry for MCP tools
 */
class ToolRegistry
{
    /**
     * The registered tools
     */
    protected array $tools = [];

    /**
     * Laravel container
     */
    protected Container $container;

    /**
     * Constructor
     */
    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }

    /**
     * Register a tool by class name or instance
     */
    public function register(string|Tool $tool): self
    {
        if (is_string($tool)) {
            $tool = $this->container->make($tool);
        }

        if (! $tool instanceof Tool) {
            throw new \InvalidArgumentException('Tool must implement Tool interface');
        }

        $this->tools[$tool->getName()] = $tool;

        return $this;
    }

    /**
     * Register multiple tools
     */
    public function registerMany(array $tools): self
    {
        foreach ($tools as $tool) {
            $this->register($tool);
        }

        return $this;
    }

    /**
     * Get all registered tools
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * Get a tool by name
     */
    public function getTool(string $name): ?Tool
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Get tool schemas for MCP
     */
    public function getToolSchemas(): array
    {
        $schemas = [];
        foreach ($this->tools as $tool) {
            $schemas[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }

        return $schemas;
    }
}
