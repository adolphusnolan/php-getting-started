<?php
include "db.php";

session_start();
$obj_files = json_decode($_POST["data"]);

$max_file_size_uploaded = 10000;

/**START OF THE ACTIONS FOR ADD DOCUMENT IN FORMS IN EDIT PROJECT  */
/** BEGIN ADD DOCUMENT PROJECT IN FORMS */
if ($obj_files->action == "upload_new_document") {

    // Array to hold validation warning messages
    $validation_warning_messages = [];

    /** CHECK FILE UPLOADED */
    $file_error = false; // Flag for file errors
    $invalid_ext = false; // Flag for invalid file extension
    $invalid_size = false; // Flag for invalid file size

    // CHECK IF FILE IS EMPTY
    $add_case_attachment_nb = count($_FILES['files']['name']); // Number of uploaded files
    if ($add_case_attachment_nb == 0) {
        $file_error = true; // Set file error if no files are uploaded
    }

    // Loop through each uploaded file
    for ($index = 0; $index < $add_case_attachment_nb; $index++) {
        if (isset($_FILES['files']['name'][$index]) && $_FILES['files']['name'][$index] != '') {
            // Get file name and size
            $filename = $_FILES['files']['name'][$index];

            $file_size = number_format(floatval($_FILES['files']['size'][$index] / 1000), 2); // Convert size to KB
            $file_size = str_replace(",", "", $file_size);

            // Get file extension
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); // Extract extension
            $valid_ext = array("html"); // List of valid extensions

            // Check if the file has a valid extension
            if (!in_array($ext, $valid_ext)) {
                $invalid_ext = true;
                $file_error = true; // Set error if extension is invalid
            }

            // Check if file size exceeds the allowed limit
            if (intval($file_size) > $max_file_size_uploaded) {
                $invalid_size = true;
                $file_error = true; // Set error if file size is too large
            }
        }
    }

    // Handle file errors
    if ($file_error) {
        if ($invalid_ext) {
            $validation_warning_messages[] = "add_file_document-file_ext"; // Invalid file extension error
        } elseif ($invalid_size) {
            $validation_warning_messages[] = "add_file_document-file_size"; // Invalid file size error
        } else {
            $validation_warning_messages[] = "add_file_document-file_empty"; // No file uploaded error
        }
    }

    // If there are validation errors, send a response with error messages
    if (sizeof($validation_warning_messages) > 0) {
        $arr = array(
            'status' => "input error",
            'array_error' => $validation_warning_messages,
        );
        header('Content-type: application/json');
        echo json_encode($arr);
        exit;
    }

    // Process the uploaded files

    // If file is valid, process it
    if (count($_FILES['files']['name']) > 0) {
        for ($index = 0; $index < count($_FILES['files']['name']); $index++) {
            if (isset($_FILES['files']['name'][$index]) && $_FILES['files']['name'][$index] != '') {
                // Access the uploaded file directly from the temporary location
                $file_tmp = $_FILES['files']['tmp_name'][$index];

                // Check if the uploaded file is HTML
                // Check if the uploaded file is HTML
                if (pathinfo($filename, PATHINFO_EXTENSION) === 'html') {
                    // Read the content of the HTML file
                    $html_content = file_get_contents($file_tmp);

                    // Decode HTML entities to normal characters
                    $decoded_content = html_entity_decode($html_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');




                    // Load the HTML into DOMDocument
                    $doc = new DOMDocument();
                    libxml_use_internal_errors(true); // Suppress warnings (important for malformed HTML)
                    $doc->loadHTML($decoded_content); // Load the HTML content
                    libxml_clear_errors(); // Clear any errors after parsing

                    // Create a DOMXPath object to query the DOM
                    $xpath = new DOMXPath($doc);


                    // Find the <h3> tag that contains "Reference #:"
                    $reference_value = null; // Initialize the reference value variable

                    // Get all <h3> tags
                    $h3_tags = $doc->getElementsByTagName('h3');
                    foreach ($h3_tags as $h3) {
                        // Check if the text content matches "Reference #:"
                        if (trim($h3->nodeValue) == 'Reference #:') {
                            // Find the next <p> tag (with class "ng-binding") under the same parent
                            $p = $h3->parentNode->getElementsByTagName('p')->item(0); // Get the first <p> element
                            if ($p) {
                                // Get the text content of the <p> tag (the reference value)
                                $reference_value = trim($p->nodeValue);
                                break;
                            }
                        }
                    }
                    // Display the extracted reference value
                    // if ($reference_value) {
                    //     echo "Reference Value: " . $reference_value;
                    // } else {
                    //     echo "Reference not found in the HTML.";
                    // }

                    //------------------------------------------------------
                    // Find the report date
                    $report_date = null;
                    foreach ($h3_tags as $h3) {
                        if (trim($h3->nodeValue) == 'Report Date:') {
                            $p = $h3->parentNode->getElementsByTagName('ng')->item(0); // Get the <ng> element
                            if ($p) {
                                $report_date = trim($p->nodeValue);
                                break;
                            }
                        }
                    }

                    // Display the extracted reference value
                    // if ($report_date) {
                    //     echo "Report Date: " . $report_date;
                    // } else {
                    //     echo "Report Date not found in the HTML.";
                    // }
                    //------------------------------------------------------

                    // Locate the div with the specific class
                    $div = $xpath->query("//div[contains(@class, 'sub_header')]");

                    $furnisher="";
                    if ($div->length > 0) {
                        // Get the text content of the div and trim extra spaces
                        $rawText = $div->item(0)->textContent;
                        $cleanText = trim(preg_replace('/\s+/', ' ', $rawText)); // Replace multiple spaces/newlines with a single space

                        $furnisher=$cleanText;

                    } 
                    //------------------------------------------------------
                    $account="000";

                    // Display the extracted reference value
                    // if ($account) {
                    //     echo "Account: " . $account;
                    // } else {
                    //     echo "Account not found in the HTML.";
                    // }
                    //------------------------------------------------------
                    $parsedData = [];

                    // Query the specific table by class name
                    $table = $xpath->query("//table[contains(@class, 're-even-odd rpt_content_table rpt_content_header rpt_table4column ng-scope')]")->item(0);

                    if ($table) {

                        // Extract headers
                        $headers = [];
                        $headerNodes = $xpath->query(".//th", $table);
                        foreach ($headerNodes as $header) {
                            $headers[] = trim($header->nodeValue);
                        }

                        // Extract rows
                        $rowNodes = $xpath->query(".//tr", $table);
                        foreach ($rowNodes as $index => $rowNode) {
                            $cells = $xpath->query(".//td", $rowNode);
                            if ($cells->length > 0) {
                                $rowData = [];
                                foreach ($cells as $i => $cell) {
                                    $rowData[$headers[$i] ?? ""] = trim($cell->textContent);
                                }
                                $parsedData[] = $rowData;
                            }
                        }
                    }

                    // Prepare the INSERT query
                    $query = 
                        "INSERT INTO accounthistory (reference, reportdate, creditor, account, category, transunion, experian, equifax, account_history_created_date)
                            VALUES (:reference, :report_date, :furnisher, :account, :category, :chester_pa, :allen_tx, :atlanta_ga, :account_history_created_date)
                        ";
                    $stmt = $pdo->prepare($query);

                    // Default report date (you can change this)
                    $account_history_created_date = date('Y-m-d');

                    $data_stored = [];

                    try {
                        // Loop through $table_data and insert each entry
                        foreach ($parsedData as $row) {
                            $key = str_replace(":", "", $row['']); // Clean up location name, but allow other special characters
                            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); // Optionally sanitize to prevent XSS on output
                        
                            // For other fields, you can safely leave special characters without escaping them for SQL insertion
                            $chester_pa = $row['ChesterPA'] ?? "BLANK";
                            $allen_tx = $row['AllenTX'] ?? "BLANK";
                            $atlanta_ga = $row['AtlantaGA'] ?? "BLANK";
                            $category = $row[''] ?? "BLANK";
                        
                            // Execute the prepared statement
                            $stmt->execute([
                                ':reference' => $reference_value ?? "BLANK",
                                ':report_date' => $report_date ?? "BLANK",
                                ':furnisher' => $furnisher ?? "BLANK",
                                ':account' => $account ?? "BLANK",
                                ':category' => $category ?? "BLANK",
                                ':chester_pa' => $chester_pa,
                                ':allen_tx' => $allen_tx,
                                ':atlanta_ga' => $atlanta_ga,
                                ':account_history_created_date' => $account_history_created_date ?? "BLANK",
                            ]);
                        
                            // If execution is successful, add the stored data to $data_stored
                            $data_stored[] = [
                                'reference' => $reference_value ?? "BLANK",
                                'report_date' => $report_date ?? "BLANK",
                                'furnisher' => $furnisher ?? "BLANK",
                                'account' => $account ?? "BLANK",
                                'category' => $category ?? "BLANK",
                                'chester_pa' => $chester_pa,
                                'allen_tx' => $allen_tx,
                                'atlanta_ga' => $atlanta_ga,
                            ];
                        }
                        

                    } catch (Exception $e) {
                        // Handle exceptions (e.g., log the error, return a failure response, etc.)
                        // return ['error' => $e->getMessage()];
                        $arr = array(
                            'status' => "error",
                            'message' => "There was a problem inserting data into the database. Please contact your administrator.",
                        );
                        header('Content-type: application/json');
                        echo json_encode($arr);

                        // echo $e->getMessage();
                        //exit;
                    }
                    // echo "Data inserted successfully.";

                    $arr = array(
                        'status' => "success",
                        'message' => "Document uploaded successfully!",
                        'data_stored' => $data_stored
                    );
                    header('Content-type: application/json');
                    echo json_encode($arr);


                } else {
                    echo json_encode(['status' => 'error', 'message' => 'The uploaded file is not an HTML file.']);
                }
            }
        }

    }
}

/** END ADD DOCUMENT PROJECT IN FORMS */