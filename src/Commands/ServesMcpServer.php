<?php

namespace InnoGE\LaravelMcp\Commands;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Server\MCPServerFactory;
use InnoGE\LaravelMcp\Tools\Tool;
use phpDocumentor\Reflection\Types\ClassString;
use Throwable;

trait ServesMcpServer
{
    /**
     * Serve an MCP server with STDIO transport
     */
    public function serveMcp(string $serverName, string $serverVersion = '1.0.0'): int
    {
        $this->debug('Starting MCP server...');

        // Create MCP server with tools and resources
        $server = MCPServerFactory::createFromCommand(
            $this,
            $serverName,
            $serverVersion,
            $this->getTools(),
            $this->getResources(),
            $this->isDebug()
        );

        // Clear output buffers to ensure clean STDIO communication
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Run the server
        try {
            $server->run();
        } catch (Throwable $e) {
            // If debug is enabled, the error will be logged by the CommandLogger
            // Otherwise, just return error code
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Output debug information to STDERR
     */
    public function debug(string $message): void
    {
        if ($this->isDebug()) {
            fwrite(STDERR, "[DEBUG] {$message}\n");
        }
    }

    /**
     * Check if debug mode is enabled
     */
    public function isDebug(): bool
    {
        return true;
    }

    /**
     * Get the tools to be registered with the MCP server
     *
     * @return array<ClassString<Tool>>
     */
    private function getTools(): array
    {
        return [];
    }

    /**
     * Get the resource providers to be registered with the MCP server
     *
     * @return ResourceProviderInterface[] Array of resource provider instances
     */
    private function getResources(): array
    {
        return [];
    }
}
