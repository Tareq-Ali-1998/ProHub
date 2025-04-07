<?php

require_once dirname(__DIR__) . '/config.php';

/**
 * Handles HTTP Basic Authentication for API requests.
 */
class Authenticator {
    /**
     * Validates the request's Authorization header credentials.
     * 
     * @return bool True if authenticated, false otherwise.
     */
    public static function authenticate(): bool {
        // Check for Authorization header
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return false;
        }

        // Extract credentials
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $encodedCredentials = substr($authHeader, 6); // Remove "Basic " prefix
        $decodedCredentials = base64_decode($encodedCredentials);
        list($username, $password) = explode(':', $decodedCredentials, 2);

        return ($username === API_USERNAME && $password === API_PASSWORD);
    }
}

?>