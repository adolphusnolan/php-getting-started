<?php

require_once 'utils/inquiries.php';

// Load environment variables
try {
    loadEnv('.env'); // Adjust the path if .env is located elsewhere
} catch (Exception $e) {
    die('<div class="error">Configuration Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Database configuration (Commented out for now)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');


/**
 * Class DatabaseHandler
 * Handles database connections and operations.
 */
class DatabaseHandler
{
    private $pdo;

    public function __construct()
    {
        // Database connection commented out for now
        
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";";
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
        
    }

    public function insertInquiries($inquiries)
    {
        if (empty($inquiries)) {
            throw new Exception('No inquiry records to insert.');
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO inquiries (
                    reference, report_date, creditor_name, type_of_business, date_of_inquiry, credit_bureau
                ) VALUES (
                    :reference, :report_date, :creditor_name, :type_of_business, :date_of_inquiry, :credit_bureau
                )
            ");

            foreach ($inquiries as $inquiry) {
                $stmt->execute([
                    ':reference' => $inquiry['reference'],
                    ':report_date' => $this->formatDate($inquiry['report_date']),
                    ':creditor_name' => $inquiry['creditor_name'],
                    ':type_of_business' => $inquiry['type_of_business'] ?: null,
                    ':date_of_inquiry' => $this->formatDate($inquiry['date_of_inquiry']),
                    ':credit_bureau' => $inquiry['credit_bureau'],
                ]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function formatDate($date)
    {
        // Convert MM/DD/YYYY to YYYY-MM-DD
        $dateObj = DateTime::createFromFormat('m/d/Y', $date);
        return $dateObj ? $dateObj->format('Y-m-d') : null;
    }
}

?>