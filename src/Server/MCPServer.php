<?php

namespace InnoGE\LaravelMcp\Server;

use InnoGE\LaravelMcp\Protocol\MCPProtocol;
use InnoGE\LaravelMcp\Protocol\NotificationHandler;
use InnoGE\LaravelMcp\Protocol\RequestHandler;
use InnoGE\LaravelMcp\Resources\ResourceProviderInterface;
use InnoGE\LaravelMcp\Server\NotificationHandlers\Resources\ResourceListChangedNotification;
use InnoGE\LaravelMcp\Server\NotificationHandlers\Resources\ResourceUpdatedNotification;
use InnoGE\LaravelMcp\Server\RequestHandlers\InitializeHandler;
use InnoGE\LaravelMcp\Server\RequestHandlers\Resources\ResourceListHandler;
use InnoGE\LaravelMcp\Server\RequestHandlers\Resources\ResourceReadHandler;
use InnoGE\LaravelMcp\Server\RequestHandlers\Resources\ResourceSubscribeHandler;
use InnoGE\LaravelMcp\Server\RequestHandlers\Resources\ResourceTemplatesListHandler;
use InnoGE\LaravelMcp\Server\RequestHandlers\Resources\ResourceUnsubscribeHandler;
use InnoGE\LaravelMcp\Types\InitializeParams;
use InnoGE\LaravelMcp\Types\InitializeResult;
use InnoGE\LaravelMcp\Utils\JsonRpcError;
use ReflectionClass;
use RuntimeException;

/**
 * MCPServer
 *
 * Main server class for Model Context Protocol implementation.
 * Handles server initialization, capabilities, and request/notification routing.
 */
class MCPServer
{
    /**
     * Protocol handler
     */
    private MCPProtocol $protocol;

    /**
     * Server information
     */
    private array $serverInfo;

    /**
     * Server capabilities
     */
    private ServerCapabilities $capabilities;

    /**
     * Whether the server is initialized
     */
    private bool $initialized = false;

    /**
     * Client capabilities received during initialization
     */
    private ?array $clientCapabilities = null;

    /**
     * Request handlers
     *
     * @var RequestHandler[]
     */
    private array $requestHandlers = [];

    /**
     * Notification handlers
     *
     * @var NotificationHandler[]
     */
    private array $notificationHandlers = [];

    /**
     * Constructor
     *
     * @param  MCPProtocol  $protocol  The protocol handler
     * @param  array  $serverInfo  Server information
     * @param  ServerCapabilities|null  $capabilities  Server capabilities
     */
    public function __construct(MCPProtocol $protocol, array $serverInfo, ?ServerCapabilities $capabilities = null)
    {
        $this->protocol = $protocol;
        $this->serverInfo = $serverInfo;
        $this->capabilities = $capabilities ?? new ServerCapabilities;

        // Register the initialize handler
        $this->registerRequestHandler(new InitializeHandler($this));
    }

    /**
     * Create a new server with default settings
     *
     * @param  MCPProtocol  $protocol  The protocol handler
     * @param  string  $name  Server name
     * @param  string  $version  Server version
     * @param  ServerCapabilities|null  $capabilities  Server capabilities
     */
    public static function create(
        MCPProtocol $protocol,
        string $name,
        string $version,
        ?ServerCapabilities $capabilities = null
    ): self {
        return new self($protocol, [
            'name' => $name,
            'version' => $version,
        ], $capabilities);
    }

    /**
     * Register a request handler
     *
     * @param  RequestHandler  $handler  The handler to register
     */
    public function registerRequestHandler(RequestHandler $handler): void
    {
        $this->requestHandlers[] = $handler;
        $this->protocol->registerRequestHandler($handler);
    }

    /**
     * Register a notification handler
     *
     * @param  NotificationHandler  $handler  The handler to register
     */
    public function registerNotificationHandler(NotificationHandler $handler): void
    {
        $this->notificationHandlers[] = $handler;
        $this->protocol->registerNotificationHandler($handler);
    }

    /**
     * Start the server
     */
    public function start(): void
    {
        $this->protocol->connect();
    }

    /**
     * Stop the server
     */
    public function stop(): void
    {
        $this->protocol->disconnect();
    }

    /**
     * Process the server input
     */
    public function processInput(): void
    {
        if ($this->protocol instanceof MCPProtocol) {
            // We need to ensure we're using a transport class that has processInput
            // This is a bit of a hack, but it works for now
            $reflection = new ReflectionClass($this->protocol);
            $transportProperty = $reflection->getProperty('transport');
            $transportProperty->setAccessible(true);
            $transport = $transportProperty->getValue($this->protocol);

            if (method_exists($transport, 'processInput')) {
                $transport->processInput();
            }
        }
    }

    /**
     * Run the server in a loop
     */
    public function run(): void
    {
        $this->start();

        if ($this->protocol instanceof MCPProtocol) {
            // We need to ensure we're using a transport class that has startReadLoop
            // This is a bit of a hack, but it works for now
            $reflection = new ReflectionClass($this->protocol);
            $transportProperty = $reflection->getProperty('transport');
            $transportProperty->setAccessible(true);
            $transport = $transportProperty->getValue($this->protocol);

            if (method_exists($transport, 'startReadLoop')) {
                $transport->startReadLoop();
            } else {
                // Fall back to our own loop
                while (true) {
                    $this->processInput();
                    usleep(10000); // 10ms
                }
            }
        }
    }

    /**
     * Handle server initialization
     *
     * @param  InitializeParams  $params  Initialization parameters
     * @return InitializeResult Initialization result
     *
     * @throws JsonRpcError If initialization fails
     */
    public function initialize(InitializeParams $params): InitializeResult
    {
        if ($this->initialized) {
            throw new JsonRpcError('Server already initialized', JsonRpcError::INVALID_REQUEST);
        }

        // Debug initialization params
        fwrite(STDERR, 'Received initialization params: '.json_encode($params)."\n");

        // Store client capabilities
        $this->clientCapabilities = $params->capabilities;

        // Mark as initialized
        $this->initialized = true;

        // Get the protocol version from params or use default
        $protocolVersion = $params->protocolVersion ?? '2024-11-05';

        // Create result with proper format
        $result = new InitializeResult(
            $this->serverInfo['name'],
            $this->serverInfo['version'],
            $this->capabilities->toArray(),
            $protocolVersion
        );

        // Debug the result we're sending back
        fwrite(STDERR, 'Sending initialization result: '.json_encode($result->toArray())."\n");

        return $result;
    }

    /**
     * Check if the server is initialized
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Get the client capabilities
     *
     * @throws RuntimeException If the server is not initialized
     */
    public function getClientCapabilities(): ?array
    {
        if (! $this->initialized) {
            throw new RuntimeException('Server not initialized');
        }

        return $this->clientCapabilities;
    }

    /**
     * Get the server capabilities
     */
    public function getCapabilities(): ServerCapabilities
    {
        return $this->capabilities;
    }

    /**
     * Get the server information
     */
    public function getServerInfo(): array
    {
        return $this->serverInfo;
    }

    /**
     * Set of resource URIs that have subscriptions
     *
     * @var array<string, bool>
     */
    private array $resourceSubscriptions = [];

    /**
     * Add a resource subscription
     *
     * @param  string  $uri  The URI to subscribe to
     */
    public function addResourceSubscription(string $uri): void
    {
        $this->resourceSubscriptions[$uri] = true;
    }

    /**
     * Remove a resource subscription
     *
     * @param  string  $uri  The URI to unsubscribe from
     */
    public function removeResourceSubscription(string $uri): void
    {
        unset($this->resourceSubscriptions[$uri]);
    }

    /**
     * Check if a resource has a subscription
     *
     * @param  string  $uri  The URI to check
     * @return bool True if the resource has a subscription
     */
    public function hasResourceSubscription(string $uri): bool
    {
        return isset($this->resourceSubscriptions[$uri]);
    }

    /**
     * Get all resource subscriptions
     *
     * @return array<string> Array of URIs with subscriptions
     */
    public function getResourceSubscriptions(): array
    {
        return array_keys($this->resourceSubscriptions);
    }

    /**
     * Send a notification that a resource has been updated
     *
     * @param  string  $uri  The URI of the resource that was updated
     */
    public function notifyResourceUpdated(string $uri): void
    {
        if ($this->hasResourceSubscription($uri)) {
            $notification = new ResourceUpdatedNotification($this->protocol);
            $notification->notify($uri);
        }
    }

    /**
     * Send a notification that the list of resources has changed
     */
    public function notifyResourceListChanged(): void
    {
        $notification = new ResourceListChangedNotification($this->protocol);
        $notification->notify();
    }

    /**
     * Set up resource feature with the specified resource provider
     *
     * @param  ResourceProviderInterface  $resourceProvider  The resource provider
     */
    public function setupResourceFeature(ResourceProviderInterface $resourceProvider): void
    {
        // Register resource request handlers
        $this->registerRequestHandler(new ResourceListHandler($resourceProvider));
        $this->registerRequestHandler(new ResourceReadHandler($resourceProvider));
        $this->registerRequestHandler(new ResourceTemplatesListHandler($resourceProvider));
        $this->registerRequestHandler(new ResourceSubscribeHandler($resourceProvider, $this));
        $this->registerRequestHandler(new ResourceUnsubscribeHandler($resourceProvider, $this));

        // Register resource notification handlers
        $listChangedNotification = new ResourceListChangedNotification($this->protocol);
        $resourceUpdatedNotification = new ResourceUpdatedNotification($this->protocol);
        $this->registerNotificationHandler($listChangedNotification);
        $this->registerNotificationHandler($resourceUpdatedNotification);
    }
}
