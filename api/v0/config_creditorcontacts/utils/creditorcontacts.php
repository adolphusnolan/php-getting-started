<?php

/**
 * Loads environment variables from a .env file into the $_ENV superglobal.
 *
 * @param string $path Path to the .env file.
 * @return void
 * @throws Exception If the .env file is not found.
 */
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
?>