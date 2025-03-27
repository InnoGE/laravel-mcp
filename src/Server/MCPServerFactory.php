<?php

namespace InnoGE\LaravelMcp\Server;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Facades\LaravelMcp;
use InnoGE\LaravelMcp\Facades\MCP;
use InnoGE\LaravelMcp\Logger\CommandLogger;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Tools\ToolRegistry;
use InnoGE\LaravelMcp\Tools\ToolServer;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating MCP servers with tools
 */
class MCPServerFactory
{
    /**
     * Create a tool-enabled MCP server with optional resource providers
     *
     * @param  string  $serverName  Server name
     * @param  string  $serverVersion  Server version
     * @param  ToolRegistry  $toolRegistry  Tool registry
     * @param  array<ResourceProviderInterface>  $resourceProviders  Resource providers
     * @param  LoggerInterface|null  $logger  Logger for debugging
     */
    public static function createWithTools(
        string $serverName,
        string $serverVersion,
        ToolRegistry $toolRegistry,
        array $resourceProviders = [],
        ?LoggerInterface $logger = null
    ): MCPServer {
        // Create server capabilities with tools and resources
        $capabilities = new ServerCapabilities;
        $capabilities->withTools(['schemas' => $toolRegistry->getToolSchemas()]);

        // Add resource capabilities if we have any resource providers
        if (! empty($resourceProviders)) {
            $capabilities->withResources(true, true);
        }

        if ($logger) {
            $logger->debug('Server capabilities created with '.count($toolRegistry->getTools()).' tools');
        }

        // Create MCP server
        $server = LaravelMcp::createStdioServer($serverName, $serverVersion, $capabilities);

        // Setup tool server with handlers
        $toolServer = new ToolServer($logger);
        $toolServer->registerHandlers($server, $toolRegistry);

        // Set up resource providers if any
        foreach ($resourceProviders as $resourceProvider) {
            $server->setupResourceFeature($resourceProvider);

            if ($logger) {
                $logger->debug('Registered resource provider: '.get_class($resourceProvider));
            }
        }

        return $server;
    }

    /**
     * Create a tool-enabled MCP server from a Laravel command
     *
     * @param  Command  $command  The Laravel command
     * @param  string  $serverName  Server name
     * @param  string  $serverVersion  Server version
     * @param  array  $tools  Array of tool class names
     * @param  array  $resourceProviders  Array of resource provider instances
     * @param  bool  $debug  Enable debug logging
     */
    public static function createFromCommand(
        Command $command,
        string $serverName,
        string $serverVersion,
        array $tools,
        array $resourceProviders = [],
        bool $debug = false
    ): MCPServer {
        // Create command logger
        $logger = new CommandLogger($command, $debug);

        // Create tool registry and register tools
        $toolRegistry = new ToolRegistry(app());

        foreach ($tools as $toolClass) {
            $toolRegistry->register($toolClass);
        }

        $logger->debug('Registered tools: '.implode(', ', array_keys($toolRegistry->getTools())));

        // Create the server
        return self::createWithTools($serverName, $serverVersion, $toolRegistry, $resourceProviders, $logger);
    }
}
