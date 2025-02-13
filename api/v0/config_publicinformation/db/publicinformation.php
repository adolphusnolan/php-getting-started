<?php

function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception(".env file not found at path: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove surrounding quotes if any
            $value = trim($value, "\"'");

            $_ENV[$key] = $value;
        }
    }
}

// Load environment variables
try {
    loadEnv('./configs/.env'); // Adjust the path if .env is located elsewhere
} catch (Exception $e) {
    die('<div class="error">Configuration Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');


/**
 * Class DatabaseHandler
 * Handles database connections and operations.
 */
class Database
{
    private $pdo;

    public function __construct()
    {
        // Database connection commented out for now
        
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";";
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
        
    }

    /**
    * Inserts data into the database.
    *
    * @param array $records An array of records to insert.
    * @return void
    */
    public function insertRecords($records)
    {
        if (empty($records)) {
            return;
        }

        $sql = "INSERT INTO publicinformation (reference, report_date, type, chester_pa, allen_tx, atlanta_ga)
            VALUES (:reference, :report_date, :type, :chester_pa, :allen_tx, :atlanta_ga)";

        $stmt = $this->pdo->prepare($sql);

        foreach ($records as $record) {
            $stmt->execute([
                ':reference'   => $record['Reference'],
                ':report_date' => $record['ReportDate'],
                ':type'        => $record['Type'],
                ':chester_pa'  => $record['ChesterPA'],
                ':allen_tx'    => $record['AllenTX'],
                ':atlanta_ga'  => $record['AtlantaGA'],
            ]);
        }
    }
    private function formatDate($date)
    {
        $dateObj = DateTime::createFromFormat('Y/m/d', $date);
        return $dateObj ? $dateObj->format('d-m-Y') : null;
    }
}

?>