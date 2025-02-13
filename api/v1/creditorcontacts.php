<?php

// Include the ReportParser class
require_once 'parser/creditorcontacts.php';
require_once 'db/creditorcontacts.php';



// Main execution flow
try {
    // Initialize variables to store errors and messages
    $errors = [];
    $messages = [];
    $inquiries = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['html_file'])) {
        $uploader = new FileUploader($_FILES['html_file']);

        if ($uploader->validate()) {
            try {
                $htmlContent = $uploader->getContent();

                $parser = new HTMLParser($htmlContent);
                $parser->parse();
                $inquiries = $parser->getInquiries();

                if (empty($inquiries)) {
                    $messages[] = 'No inquiry records found.';
                } else {
                    // Database insertion code commented out
                    
                    $dbHandler = new DatabaseHandler();
                    $dbHandler->insertInquiries($inquiries);
                    $messages[] = 'Inquiry records have been successfully inserted into the database.';
                    
                    $messages[] = 'Inquiry records have been successfully parsed. Displaying below:';
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
    if (!empty($inquiries)) {
        echo '
        <table>
            <tr>
                <th>Reference</th>
                <th>Report Date</th>
                <th>Creditor Name</th>
                <th>Address</th>
                <th>Phone Number</th>
            </tr>
        ';
        foreach ($inquiries as $inquiry) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($inquiry['reference']).'</td>';
            echo '<td>'.htmlspecialchars($inquiry['report_date']).'</td>';
            echo '<td>'.htmlspecialchars($inquiry['creditor_name']).'</td>';
            echo '<td>'.htmlspecialchars($inquiry['address']).'</td>';
            echo '<td>'.htmlspecialchars($inquiry['phone_number']).'</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($inquiries) && empty($errors)) {
        // If form was submitted but no inquiries and no errors
        echo '<div class="no-data">No inquiries to display.</div>';
    }

    echo '
        </div>
    </body>
    </html>
    ';

} catch (Exception $e) {
    // In case of any unexpected exception, display it below the form
    // Since the main try block already handles known exceptions, this is for any other unforeseen errors
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
                width: 600px;
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
