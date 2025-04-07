<?php

require_once dirname(__DIR__) . '/config.php';

/**
 * Handles secure MySQL database connections and queries using prepared statements.
 * Provides methods for executing queries, transactions, and connection management.
 */
class DatabaseConnection {
    /**
     * @var mysqli The MySQL database connection instance.
     */
    private $connection;

    /**
     * Initializes a new database connection.
     *
     * @param string $host         Database server hostname or IP.
     * @param string $username     Database username.
     * @param string $password     Database password.
     * @param string $databaseName Name of the database to use.
     * 
     * @throws RuntimeException If connection fails.
     */
    public function __construct(
        string $host = DB_HOST,
        string $username = DB_USER_NAME,
        string $password = DB_USER_PASSWORD,
        string $databaseName = DB_NAME
    ) {
        // Enable mysqli exceptions globally.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->connection = new mysqli($host, $username, $password, $databaseName);
            // Set secure connection settings.
            $this->connection->set_charset("utf8mb4");
        }
        catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Database connection failed.");
        }
        
    }

    /**
     * Executes a parameterized SQL query using prepared statements.
     *
     * @param string $query The SQL query with ? placeholders.
     * @param array $params Array of parameters to bind to the query.
     * @param string $paramTypes String of parameter types (e.g., 'sis' for string, int, string).
     * 
     * @return mysqli_result|true Returns mysqli_result for SELECT-like queries, or
     * true for other queries.
     * 
     * @throws RuntimeException If query preparation, binding, or execution fails.
     */
    public function executeQuery(string $query, array $params = [], string $paramTypes = '') {
        // Step 1: Prepare the query.
        try {
            $stmt = $this->connection->prepare($query);
        } catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Query preparation failed.");
        }

        // Step 2: Bind parameters (if provided).
        if (!empty($params)) {
            try {
                $stmt->bind_param($paramTypes, ...$params);
            } catch (mysqli_sql_exception $exception) {
                throw new RuntimeException("Database Error: Parameter binding failed.");
            }
        }

        // Step 3: Execute the query.
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Query execution failed.");
        }

        // Step 4: Handle the result.
        $result = $stmt->get_result();
        $stmt->close();

        return ($result instanceof mysqli_result) ? $result : true;
        
    }

    /**
     * Starts a database transaction.
     * 
     * @throws RuntimeException If the transaction fails to begin.
     */
    public function beginTransaction(): void {
        try {
            $this->connection->begin_transaction();
        }
        catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Transaction failed to begin.");
        }
    }

    /**
     * Commits the current transaction.
     * 
     * @throws RuntimeException If the transaction fails to be comitted.
     */
    public function commitTransaction(): void {
        try {
            $this->connection->commit();
        }
        catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Transaction failed to be committed.");
        }
    }

    /**
     * Rolls back the current transaction.
     * 
     * @throws RuntimeException If the transaction fails to be rolled back.
     */
    public function rollbackTransaction(): void {
        try {
            $this->connection->rollback();
        }
        catch (mysqli_sql_exception $exception) {
            throw new RuntimeException("Database Error: Transaction failed to be rolled back.");
        }
    }

    /**
     * Gets the ID of the last inserted row.
     * 
     * @return int Last inserted ID, or 0 if no INSERT has occurred.
     */
    public function getLastInsertId(): int {
        return $this->connection->insert_id;
    }

    /**
     * Gets the raw mysqli connection (use sparingly!).
     * 
     * @return mysqli The underlying mysqli connection.
     */
    public function getRawConnection(): mysqli {
        return $this->connection;
    }

    /**
     * Closes the database connection.
     */
    public function closeConnection(): void {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Destructor ensures connection is closed when object is destroyed.
     */
    public function __destruct() {
        $this->closeConnection();
    }
}

?>