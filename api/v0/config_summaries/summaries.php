<?php

// Include the ReportParser class
require_once 'src/parser.php';
require_once 'src/db.php';


// Main execution flow
try {
    // Initialize variables to store errors and messages
    $errors = [];
    $messages = [];
    $records = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['html_file'])) {
        $uploader = new FileUploader($_FILES['html_file']);

        if ($uploader->validate()) {
            try {
                $htmlContent = $uploader->getContent();

                $parser = new HTMLParser($htmlContent);
                $records = $parser->parse();

                if (!empty($records)) {
                    // Database insertion
                    $dbHandler = new Database();
                    $dbHandler->insertRecords($records);
                    $messages[] = 'Records have been successfully inserted into the database.';
                    $messages[] = 'Parsed records are displayed below:';
                } else {
                    $messages[] = 'No records found.';
                }

                // Cleanup temporary file
                $uploader->cleanup();
            } catch (Exception $e) {
                // Catch parsing exceptions and add to errors
                $errors[] = 'Error: ' . $e->getMessage();
                // Cleanup temporary file even if parsing fails
                $uploader->cleanup();
            }
        } else {
            // Add uploader errors to errors array
            $errors = array_merge($errors, $uploader->getErrors());
        }
    }

    // Display the HTML form with CSS styling
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Upload HTML File</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
                margin: 0;
                padding: 0;
            }
            .container {
                width: fit-content;
                margin: 50px auto;
                background-color: #fff;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            h2 {
                text-align: center;
                margin-bottom: 30px;
            }
            label {
                display: block;
                margin-bottom: 10px;
                font-weight: bold;
            }
            input[type="file"] {
                width: 100%;
                padding: 8px;
                margin-bottom: 20px;
            }
            button {
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                color: #fff;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
            }
            button:hover {
                background-color: #45a049;
            }
            .message {
                text-align: center;
                padding: 10px;
                margin-top: 20px;
                background-color: #dff0d8;
                color: #3c763d;
                border-radius: 5px;
            }
            .error {
                text-align: center;
                padding: 10px;
                margin-top: 20px;
                background-color: #f2dede;
                color: #a94442;
                border-radius: 5px;
            }
            .error-list {
                margin-top: 10px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 30px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .no-data {
                text-align: center;
                margin-top: 20px;
                color: #555;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Upload HTML File</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="html_file">Select HTML file to upload:</label>
                <input type="file" name="html_file" id="html_file" accept=".html" required>
                <button type="submit">Upload and Process</button>
            </form>
    ';

    // Display errors if any
    if (!empty($errors)) {
        echo '<div class="error-list">';
        foreach ($errors as $error) {
            echo '<div class="error">'.htmlspecialchars($error).'</div>';
        }
        echo '</div>';
    }

    // Display messages if any
    if (!empty($messages)) {
        foreach ($messages as $message) {
            echo '<div class="message">'.htmlspecialchars($message).'</div>';
        }
    }

    // Display parsed data
    if (!empty($records)) {
        echo '
        <table>
            <tr>
                <th>Reference</th>
                <th>Report Date</th>
                <th>Type</th>
                <th>ChesterPA</th>
                <th>AllenTX</th>
                <th>AtlantaGA</th>
            </tr>
        ';
        foreach ($records as $record) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($record['Reference']).'</td>';
            echo '<td>'.htmlspecialchars($record['ReportDate']).'</td>';
            echo '<td>'.htmlspecialchars($record['Type']).'</td>';
            echo '<td>'.htmlspecialchars($record['ChesterPA']).'</td>';
            echo '<td>'.htmlspecialchars($record['AllenTX']).'</td>';
            echo '<td>'.htmlspecialchars($record['AtlantaGA']).'</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($records) && empty($errors)) {
        // If form was submitted but no records and no errors
        echo '<div class="no-data">No records to display.</div>';
    }

    echo '
        </div>
    </body>
    </html>
    ';

} catch (Exception $e) {
    // In case of any unexpected exception, display it below the form
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Upload HTML File</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
                margin: 0;
                padding: 0;
            }
            .container {
                width: fit-content;
                margin: 50px auto;
                background-color: #fff;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            h2 {
                text-align: center;
                margin-bottom: 30px;
            }
            label {
                display: block;
                margin-bottom: 10px;
                font-weight: bold;
            }
            input[type="file"] {
                width: 100%;
                padding: 8px;
                margin-bottom: 20px;
            }
            button {
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                color: #fff;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
            }
            button:hover {
                background-color: #45a049;
            }
            .error {
                text-align: center;
                padding: 10px;
                margin-top: 20px;
                background-color: #f2dede;
                color: #a94442;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Upload HTML File</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="html_file">Select HTML file to upload:</label>
                <input type="file" name="html_file" id="html_file" accept=".html" required>
                <button type="submit">Upload and Process</button>
            </form>
            <div class="error">An unexpected error occurred: '.htmlspecialchars($e->getMessage()).'</div>
        </div>
    </body>
    </html>
    ';
}
?>