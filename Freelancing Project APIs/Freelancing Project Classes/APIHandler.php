<?php
/**
 * Class APIHandler
 *
 * A class that handles HTTP requests as the server side and communicates with a MySQL database.
 * Provides functionality for authentication, request validation, database connection,
 * query execution, and response generation and more.
 */

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/Utilities.php';

class APIHandler {
    private $databaseHost;
    private $databaseUsername;
    private $databasePassword;
    private $databaseName;
    private $databaseConnection;

    /**
     * Constructor function.
     */
    public function __construct() {
        $this->databaseHost = 'localhost';
        $this->databaseUsername = 'root';
        $this->databasePassword = 'tareq12345changed';
        $this->databaseName = 'db_a993c8_freelanchanged';
        // If I don't set $this->databaseConnection attribute to any value, then it will take the default value of null
		
		/*
		$this->databaseHost = 'MYSQL8001.site4now.net';
        $this->databaseUsername = 'a993c8_freelan';
        $this->databasePassword = 'tareq1912mahmoud';
        $this->databaseName = 'db_a993c8_freelan';
        // If I don't set $this->databaseConnection attribute to any value, then it will take the default value of null
		*/
		
		
    }

    /**
     * Retrieves all the request headers.
     *
     * Returns an associative array containing all the request headers.
     */
    public function getRequestHeaders() {
        return getallheaders();
    }

    // I need this function in multiple sections of the code, so it's very important.
    public function getDatabaseConnection() {
        return $this->databaseConnection;
    }

    /**
     * Checks the request's authorization header to ensure that a valid username and password
     * have been provided.
     */
    public function authorizeRequestUsernameAndPassword() {
        // Check for username and password in headers
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401);
            $this->sendRequestBody(false, 'Authorization header missing', null);
            exit();
        }
        list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        if ($username !== 'tareq' || $password !== 'mahmood') {
            http_response_code(401);
            $this->sendRequestBody(false, 'Invalid username or password in the header of the API request', null);
            exit();
        }
    }

    /**
     * I want to accept the request body if and only if it's empty or it's not empty and a valid JSON format in the same time
     * Returns the request body as an associative array, and it could be null (in case if the request body is empty)
     */
    // TODO, I should rename it to getRequestJSONBody() because it's checking if the body is JSON or not.
    public function getRequestBody() {
        $currentRequestBody = file_get_contents('php://input');
        // When you use a falsy value like null or false in a condition, it will be treated as false. This means that
        // the condition if ($this->databaseConnection) will evaluate to false if $this->databaseConnection
        // is null, false, 0, an empty string '', or an array with no elements.
        if (!Utilities::isValidJson($currentRequestBody)) {
            http_response_code(400);
            $this->sendRequestBody(false, 'The request body is not a valid JSON', null);
            exit();
            
        }
        // The second parameter is set to true in the following function to make it return an associative array.
        return json_decode($currentRequestBody, true);
    }
    

    /**
     * Validates that the database connection has been established successfully.
     * Returns 500 as the response status code if there is no connection.
     */
    public function validateDatabaseConnection() {
        if (!$this->databaseConnection) {
            http_response_code(500);
            $this->sendRequestBody(false, 'Database connection error', null);
            exit();
        }
    }

    /**
     * Establishes a connection to the MySQL database.
     * Returns 500 as the response status code if the connection cannot be established.
     */
    public function connectToMySQLDatabase() {
        try {
            $this->databaseConnection = mysqli_connect($this->databaseHost, $this->databaseUsername,
                                                       $this->databasePassword, $this->databaseName);
            $this->validateDatabaseConnection();
        }
        catch(Exception $exception) {
            http_response_code(500);
            $this->sendRequestBody(false, 'Database connection error', null);
            exit();
        }
    }

    /**
     * Executes a MySQL query and returns the query result.
     * Returns 500 as the response status code if the query execution fails.
     *
     * param string $databaseQuery as the query to execute.
     * Returns mysqli_result object, which is an instance of the mysqli_result class in PHP, so it
     * returns $queryResult as the result of the $databaseQuery execution.
     */
    public function executeQuery($databaseQuery) {
        try {
            $this->validateDatabaseConnection();
            $queryResult = mysqli_query($this->databaseConnection, $databaseQuery);
            if (!$queryResult) {
                http_response_code(500);
                $this->sendRequestBody(false, 'Database Query execution error', null);
                exit();
            }
            return $queryResult;
        }
        catch (Exception $exception) {
            http_response_code(500);
            $this->sendRequestBody(false, 'Database Query execution error', null);
            exit();
        }
    }

    /**
     * Sets the headers for the response to "application/json" to indicate that the response
     * body is a JSON file.
     */
    public function setHeadersForTheResponse() {
        header('Content-Type: application/json');
    }

    /**
     * Sends the response body as JSON.
     * param array $data as an associative array representing the response body.
     */
    public function sendRequestBody($status, $message, $data) {
        $this->setHeadersForTheResponse();
        echo json_encode(['status' => $status, 'message' => $message ,'data' => $data], JSON_PRETTY_PRINT);
    }
}
?>
