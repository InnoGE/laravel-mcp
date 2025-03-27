<?php

namespace InnoGE\LaravelMcp;

use InnoGE\LaravelMcp\Protocol\MCPProtocol;
use InnoGE\LaravelMcp\Server\MCPServer;
use InnoGE\LaravelMcp\Server\NotificationHandlers\InitializedHandler;
use InnoGE\LaravelMcp\Server\ServerCapabilities;
use InnoGE\LaravelMcp\Transports\StdioTransport;

class LaravelMcp {

    /**
     * Create a new MCP server with STDIO transport
     *
     * @param  string  $name  Server name
     * @param  string  $version  Server version
     * @param  ServerCapabilities|null  $capabilities  Server capabilities
     */
    public function createStdioServer(
        string $name,
        string $version,
        ?ServerCapabilities $capabilities = null
    ): MCPServer {
        $transport = new StdioTransport;
        $protocol = new MCPProtocol($transport);

        return $this->createServer($protocol, $name, $version, $capabilities);
    }

    /**
     * Create a new MCP server with the given transport
     *
     * @param  MCPProtocol  $protocol  The protocol handler
     * @param  string  $name  Server name
     * @param  string  $version  Server version
     * @param  ServerCapabilities|null  $capabilities  Server capabilities
     */
    public function createServer(
        MCPProtocol $protocol,
        string $name,
        string $version,
        ?ServerCapabilities $capabilities = null
    ): MCPServer {
        $server = MCPServer::create($protocol, $name, $version, $capabilities);

        // Register the initialized notification handler
        $server->registerNotificationHandler(new InitializedHandler($server));

        return $server;
    }
}
