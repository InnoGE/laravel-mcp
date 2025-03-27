<?php

namespace InnoGE\LaravelMcp\Utils;

use Exception;

/**
 * JsonRpcError
 *
 * Custom exception class for JSON-RPC errors in the MCP protocol.
 */
class JsonRpcError extends Exception
{
    /**
     * Standard JSON-RPC error codes
     */
    public const PARSE_ERROR = -32700;

    public const INVALID_REQUEST = -32600;

    public const METHOD_NOT_FOUND = -32601;

    public const INVALID_PARAMS = -32602;

    public const INTERNAL_ERROR = -32603;

    /**
     * Custom error codes should use the range -32000 to -32099
     */
    public const SERVER_ERROR_START = -32099;

    public const SERVER_ERROR_END = -32000;

    /**
     * Additional error data
     *
     * @var mixed
     */
    private $errorData;

    /**
     * Error code
     *
     * @var int
     */
    protected $code;

    /**
     * Constructor
     *
     * @param  string  $message  Error message
     * @param  int  $code  Error code
     * @param  mixed  $data  Additional error data
     */
    public function __construct(string $message, int $code, $data = null)
    {
        parent::__construct($message, $code);
        $this->errorData = $data;
    }

    /**
     * Get the additional error data
     *
     * @return mixed
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * Get the error as an array for JSON-RPC response
     */
    public function toArray(): array
    {
        $error = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if ($this->errorData !== null) {
            $error['data'] = $this->errorData;
        }

        return $error;
    }
}
