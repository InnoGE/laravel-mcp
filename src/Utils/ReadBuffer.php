<?php

namespace InnoGE\LaravelMcp\Utils;

use Exception;

/**
 * ReadBuffer for parsing JSON-RPC messages from stdin/stdout streams
 *
 * This class handles the message format used by MCP's JSON-RPC implementation,
 * which includes content-length headers followed by JSON content.
 */
class ReadBuffer
{
    /** @var string Current buffer content */
    private $buffer = '';

    /**
     * Append data to the buffer
     *
     * @param  string  $data  Data to append
     */
    public function append(string $data): void
    {
        $this->buffer .= $data;
    }

    /**
     * Read a message from the buffer
     *
     * @return array|null JSON-RPC message or null if no complete message is available
     *
     * @throws Exception If the message is invalid
     */
    public function readMessage(): ?array
    {
        // Check if we have a complete header
        $headerEnd = strpos($this->buffer, "\r\n\r\n");
        if ($headerEnd === false) {
            return null;
        }

        // Parse the header
        $header = substr($this->buffer, 0, $headerEnd);
        $contentLengthMatch = [];
        if (! preg_match('/Content-Length: (\d+)/i', $header, $contentLengthMatch)) {
            throw new Exception("Invalid message header: $header");
        }

        $contentLength = (int) $contentLengthMatch[1];
        $contentStart = $headerEnd + 4; // Skip header and empty line

        // Check if we have the full content
        $bufferLength = strlen($this->buffer);
        if ($bufferLength < $contentStart + $contentLength) {
            return null;
        }

        // Extract the content
        $content = substr($this->buffer, $contentStart, $contentLength);

        // Remove the processed message from the buffer
        $this->buffer = substr($this->buffer, $contentStart + $contentLength);

        // Parse the JSON content
        $message = json_decode($content, true);
        if ($message === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON message: '.json_last_error_msg());
        }

        return $message;
    }

    /**
     * Clear the buffer
     */
    public function clear(): void
    {
        $this->buffer = '';
    }
}
