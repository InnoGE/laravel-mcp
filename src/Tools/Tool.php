<?php

namespace InnoGE\LaravelMcp\Tools;

/**
 * Tool interface for MCP tools
 */
interface Tool
{
    /**
     * Get the tool name
     */
    public function getName(): string;

    /**
     * Get the tool description
     */
    public function getDescription(): string;

    /**
     * Get the input schema for the tool
     */
    public function getInputSchema(): array;

    /**
     * Execute the tool with the provided arguments
     *
     * @return string|array The result of the tool execution
     */
    public function execute(array $arguments): mixed;
}
