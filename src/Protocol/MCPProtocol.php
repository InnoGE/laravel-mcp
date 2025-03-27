<?php

namespace InnoGE\LaravelMcp\Protocol;

use Exception;
use InnoGE\LaravelMcp\Transports\TransportInterface;
use InnoGE\LaravelMcp\Utils\JsonRpcError;
use InvalidArgumentException;
use RuntimeException;

/**
 * MCPProtocol
 *
 * Core protocol handler for the Model Context Protocol.
 * Implements JSON-RPC 2.0 for message formatting and handling.
 */
class MCPProtocol
{
    /**
     * Transport used for communication
     */
    private TransportInterface $transport;

    /**
     * Array of request handlers
     *
     * @var RequestHandler[]
     */
    private array $requestHandlers = [];

    /**
     * Array of notification handlers
     *
     * @var NotificationHandler[]
     */
    private array $notificationHandlers = [];

    /**
     * Map of pending request IDs to their callback functions
     *
     * @var array<string, callable>
     */
    private array $pendingRequests = [];

    /**
     * Counter for generating unique request IDs
     */
    private int $requestIdCounter = 1;

    /**
     * Constructor
     *
     * @param  TransportInterface  $transport  The transport to use
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
        $this->transport->onMessage([$this, 'handleMessage']);
    }

    /**
     * Connect the protocol to the transport
     */
    public function connect(): void
    {
        $this->transport->connect();
    }

    /**
     * Disconnect the protocol from the transport
     */
    public function disconnect(): void
    {
        $this->transport->disconnect();
        $this->pendingRequests = [];
    }

    /**
     * Register a request handler
     *
     * @param  RequestHandler  $handler  The handler to register
     */
    public function registerRequestHandler(RequestHandler $handler): void
    {
        $this->requestHandlers[] = $handler;
    }

    /**
     * Register a notification handler
     *
     * @param  NotificationHandler  $handler  The handler to register
     */
    public function registerNotificationHandler(NotificationHandler $handler): void
    {
        $this->notificationHandlers[] = $handler;
    }

    /**
     * Send a request and wait for a response
     *
     * @param  string  $method  The method to call
     * @param  array|null  $params  The parameters for the method
     * @param  float  $timeout  Timeout in seconds (0 for no timeout)
     * @return array The response result
     *
     * @throws Exception If the request fails
     */
    public function sendRequest(string $method, ?array $params = null, float $timeout = 30.0): array
    {
        if (! $this->transport->isConnected()) {
            throw new RuntimeException('Transport not connected');
        }

        // Generate a unique ID for this request
        $id = (string) $this->requestIdCounter++;

        // Create the JSON-RPC request
        $request = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
        ];

        if ($params !== null) {
            $request['params'] = $params;
        }

        // Create a promise for handling the response
        $promise = new class
        {
            public $resolve = null;

            public $reject = null;

            public $result = null;

            public $error = null;

            public $completed = false;
        };

        $promise->resolve = function ($result) use ($promise) {
            $promise->result = $result;
            $promise->completed = true;
        };

        $promise->reject = function ($error) use ($promise) {
            $promise->error = $error;
            $promise->completed = true;
        };

        // Store the promise callbacks
        $this->pendingRequests[$id] = $promise;

        // Send the request
        $this->transport->send($request);

        // Wait for the response with timeout
        $startTime = microtime(true);
        while (! $promise->completed) {
            // Process input to get responses
            $this->transport->processInput();

            // Check for timeout
            if ($timeout > 0 && (microtime(true) - $startTime) > $timeout) {
                unset($this->pendingRequests[$id]);
                throw new RuntimeException("Request timed out after {$timeout} seconds");
            }

            // Small sleep to prevent CPU hogging
            usleep(10000); // 10ms
        }

        // Clean up
        unset($this->pendingRequests[$id]);

        // Check for errors
        if ($promise->error !== null) {
            throw new JsonRpcError(
                $promise->error['message'] ?? 'Unknown error',
                $promise->error['code'] ?? -32000,
                $promise->error['data'] ?? null
            );
        }

        return $promise->result;
    }

    /**
     * Send a notification (without waiting for a response)
     *
     * @param  string  $method  The method to call
     * @param  array|null  $params  The parameters for the method
     *
     * @throws RuntimeException If the transport is not connected
     */
    public function sendNotification(string $method, ?array $params = null): void
    {
        if (! $this->transport->isConnected()) {
            throw new RuntimeException('Transport not connected');
        }

        // Create the JSON-RPC notification
        $notification = [
            'jsonrpc' => '2.0',
            'method' => $method,
        ];

        if ($params !== null) {
            $notification['params'] = $params;
        }

        // Send the notification
        $this->transport->send($notification);
    }

    /**
     * Handle an incoming message
     *
     * @param  array  $message  The message to handle
     */
    public function handleMessage(array $message): void
    {
        // Validate the message
        if (! isset($message['jsonrpc']) || $message['jsonrpc'] !== '2.0') {
            $this->sendErrorResponse(
                $message['id'] ?? null,
                -32600,
                'Invalid Request: Not a valid JSON-RPC 2.0 message'
            );

            return;
        }

        // Check if it's a response
        if (isset($message['id']) && (isset($message['result']) || isset($message['error']))) {
            $this->handleResponse($message);

            return;
        }

        // Check if it's a request or notification
        if (isset($message['method'])) {
            if (isset($message['id'])) {
                // It's a request
                $this->handleIncomingRequest($message);
            } else {
                // It's a notification
                $this->handleIncomingNotification($message);
            }

            return;
        }

        // Invalid message format
        if (isset($message['id'])) {
            $this->sendErrorResponse(
                $message['id'],
                -32600,
                'Invalid Request: Message format not recognized'
            );
        }
    }

    /**
     * Handle an incoming response
     *
     * @param  array  $response  The response to handle
     */
    private function handleResponse(array $response): void
    {
        $id = (string) $response['id'];

        // Check if we have a pending request with this ID
        if (! isset($this->pendingRequests[$id])) {
            // Unknown response ID, just ignore it
            return;
        }

        $promise = $this->pendingRequests[$id];

        // Check if it's an error response
        if (isset($response['error'])) {
            $promise->reject($response['error']);
        } else {
            $promise->resolve($response['result'] ?? null);
        }
    }

    /**
     * Handle an incoming request
     *
     * @param  array  $request  The request to handle
     */
    private function handleIncomingRequest(array $request): void
    {
        $method = $request['method'];
        $params = $request['params'] ?? null;
        $id = $request['id'];

        try {
            // Find a handler for this method
            foreach ($this->requestHandlers as $handler) {
                if ($handler->canHandle($method)) {
                    $result = $handler->handleRequest($method, $params);
                    $this->sendSuccessResponse($id, $result);

                    return;
                }
            }

            // No handler found
            $this->sendErrorResponse($id, -32601, "Method not found: {$method}");
        } catch (Exception $e) {
            // Handler threw an exception
            $this->sendErrorResponse($id, -32000, $e->getMessage());
        }
    }

    /**
     * Handle an incoming notification
     *
     * @param  array  $notification  The notification to handle
     */
    private function handleIncomingNotification(array $notification): void
    {
        $method = $notification['method'];
        $params = $notification['params'] ?? null;

        try {
            // Find a handler for this method
            foreach ($this->notificationHandlers as $handler) {
                if ($handler->canHandle($method)) {
                    $handler->handleNotification($method, $params);

                    return;
                }
            }

            // No handler found, just ignore the notification
        } catch (Exception $e) {
            // Handler threw an exception, log it but don't respond
            // (notifications don't expect responses)
            error_log("Error handling notification '{$method}': ".$e->getMessage());
        }
    }

    /**
     * Send a success response
     *
     * @param  mixed  $id  The ID from the request
     * @param  mixed  $result  The result to send
     *
     * @throws InvalidArgumentException If the ID is invalid
     */
    private function sendSuccessResponse($id, $result): void
    {
        if ($id === null) {
            throw new InvalidArgumentException('Cannot send a response without an ID');
        }

        $response = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];

        $this->transport->send($response);
    }

    /**
     * Send an error response
     *
     * @param  mixed  $id  The ID from the request (null for notifications)
     * @param  int  $code  The error code
     * @param  string  $message  The error message
     * @param  mixed|null  $data  Additional error data (optional)
     */
    private function sendErrorResponse(mixed $id, int $code, string $message, mixed $data = null): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($data !== null) {
            $response['error']['data'] = $data;
        }

        $this->transport->send($response);
    }
}
