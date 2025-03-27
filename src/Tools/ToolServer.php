<?php

namespace InnoGE\LaravelMcp\Tools;

use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Server\MCPServer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class ToolServer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Constructor
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger;
    }

    /**
     * Register tools with the server
     */
    public function registerHandlers(MCPServer $server, ToolRegistry $toolRegistry): void
    {
        // Register handler for tools/list
        $server->registerRequestHandler($this->createToolsListHandler($toolRegistry));

        // Register handler for tools/call and tools/execute
        $server->registerRequestHandler($this->createToolsCallHandler($toolRegistry));

        $this->logger->debug('Registered tool handlers');
    }

    /**
     * Create handler for tools/list method
     */
    protected function createToolsListHandler(ToolRegistry $toolRegistry): RequestHandler
    {
        $logger = $this->logger;

        return new class($toolRegistry, $logger) implements RequestHandler
        {
            private ToolRegistry $toolRegistry;

            private LoggerInterface $logger;

            public function __construct(ToolRegistry $toolRegistry, LoggerInterface $logger)
            {
                $this->toolRegistry = $toolRegistry;
                $this->logger = $logger;
            }

            public function canHandle(string $method): bool
            {
                return $method === 'tools/list';
            }

            public function handleRequest(string $method, ?array $params = null): array
            {
                $this->logger->debug('Handling tools/list request');

                return [
                    'tools' => $this->toolRegistry->getToolSchemas(),
                ];
            }
        };
    }

    /**
     * Create handler for tools/call and tools/execute methods
     */
    protected function createToolsCallHandler(ToolRegistry $toolRegistry): RequestHandler
    {
        $logger = $this->logger;

        return new class($toolRegistry, $logger) implements RequestHandler
        {
            private ToolRegistry $toolRegistry;

            private LoggerInterface $logger;

            public function __construct(ToolRegistry $toolRegistry, LoggerInterface $logger)
            {
                $this->toolRegistry = $toolRegistry;
                $this->logger = $logger;
            }

            public function canHandle(string $method): bool
            {
                return $method === 'tools/execute' || $method === 'tools/call';
            }

            public function handleRequest(string $method, ?array $params = null): array
            {
                $this->logger->debug("Handling tool call: {$method}");
                $this->logger->debug('Parameters: '.json_encode($params));

                if (! isset($params['name'])) {
                    throw new RuntimeException('Tool name not specified');
                }

                $tool = $this->toolRegistry->getTool($params['name']);
                if (! $tool) {
                    throw new RuntimeException("Unknown tool: {$params['name']}");
                }

                $arguments = $params['arguments'] ?? [];
                $this->logger->debug("Executing tool {$params['name']} with arguments: ".json_encode($arguments));

                $result = $tool->execute($arguments);

                // Format for different clients
                if ($method === 'tools/call') {
                    return [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => is_string($result) ? $result : json_encode($result),
                            ],
                        ],
                    ];
                } else {
                    return ['result' => $result];
                }
            }
        };
    }
}
