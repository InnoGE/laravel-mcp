<?php

namespace InnoGE\LaravelMcp\Tools\Examples;

use InnoGE\LaravelMcp\Tools\Tool;

/**
 * Example Hello Tool
 */
class HelloTool implements Tool
{
    /**
     * Get the tool name
     */
    public function getName(): string
    {
        return 'say-hello';
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Say hello to someone';
    }

    /**
     * Get the input schema for the tool
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Name to greet',
                ],
            ],
            'required' => ['name'],
        ];
    }

    /**
     * Execute the tool with the provided arguments
     */
    public function execute(array $arguments): string
    {
        $name = $arguments['name'] ?? 'world';

        return "Hello, {$name}!";
    }
}
