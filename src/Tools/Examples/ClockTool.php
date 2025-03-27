<?php

namespace InnoGE\LaravelMcp\Tools\Examples;

use InnoGE\LaravelMcp\Tools\Tool;

/**
 * Example Hello Tool
 */
class ClockTool implements Tool
{
    /**
     * Get the tool name
     */
    public function getName(): string
    {
        return 'get-time';
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Get the current time';
    }

    /**
     * Get the input schema for the tool
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object) [],
            'required' => [],
        ];
    }

    /**
     * Execute the tool with the provided arguments
     */
    public function execute(array $arguments): string
    {
        return now()->format('Y-m-d H:i:s');
    }
}
