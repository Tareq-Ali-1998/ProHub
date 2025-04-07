<?php

/**
 * Handles HTTP request/response operations.
 * All methods are static since the class has no state (no attributes).
 */
class HTTPHandler {

    /**
     * Retrieves the raw request body as a string.
     * 
     * @return string|null The raw request body as a string, or null if the input is empty.
     */
    public static function getRequestBody(): ?string {
        $bodyString = file_get_contents('php://input');
        return $bodyString === false ? null : $bodyString;
    }

    /**
     * Retrieves and validates the JSON request body.
     * 
     * @return array|null Decoded JSON as an associative array, or null if empty.
     * @throws InvalidArgumentException If JSON is invalid.
     */
    public static function getJsonRequestBody(): ?array {
        
        $jsonString = file_get_contents('php://input');
        if (empty($jsonString)) {
            return null;
        }

        $data = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('HTTP Error: Invalid JSON in request body.');
        }

        return $data;
    }

    /**
     * Sends a JSON response with status, message, and optional data.
     * 
     * @param int $statusCode HTTP status code (e.g., 200, 404).
     * @param bool $success Whether the request succeeded.
     * @param string $message Descriptive message.
     * @param mixed $data Optional data payload.
     */
    public static function sendResponse(
        int $statusCode,
        bool $success,
        string $message,
        mixed $data = null
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

}