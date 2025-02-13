<?php
include "../db/creditscores.php";

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
                    //echo json_encode($doc);


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

                    // // Display the extracted reference value
                    // if ($reference_value) {
                    //     echo "Reference Value: " . $reference_value;
                    // } else {
                    //     echo "Reference not found in the HTML.";
                    // }

                    // Initialize an array to store table data
                    $table_data = [];

                    // Flag to track if the second table should be skipped
                    $skip_second_table = false;

                    // Find all tables in the DOM
                    //$tables = $doc->getElementsByTagName('table');
                    //$tables = $doc->querySelector('#CreditScore')->getElementsByTagName('table');
                    $tables = $doc->getElementById('CreditScore')->getElementsByTagName('table');
                    //$tables = $doc->getElementsByTagName('table');
                    //echo json_encode($doc);
                    //exit;
                    foreach ($tables as $table) {
                        // Check if the table has a specific class
                        if ($table->hasAttribute('class') && strpos($table->getAttribute('class'), 're-even-odd rpt_content_table rpt_content_header rpt_table4column') !== false) {

                            // Skip the second table if we've already processed one with this class
                            if ($skip_second_table) {
                                continue; // Skip further processing for this table
                            }

                            // Mark that we've seen the first table and will skip the next one
                            $skip_second_table = true;

                            // Parse this table (first instance of the table with the class)
                            $rows = $table->getElementsByTagName('tr'); // Get all rows
                            $header_columns = []; // Array to store column headers

                            foreach ($rows as $rowIndex => $row) {
                                $columns = $row->getElementsByTagName('td'); // Get all cells in the row
                                $header_cells = $row->getElementsByTagName('th'); // Get all header cells

                                // Extract header data (column titles like TransUnion, Experian, Equifax)
                                if ($rowIndex == 0) {
                                    foreach ($header_cells as $header_cell) {
                                        $header_columns[] = trim($header_cell->nodeValue); // Store header titles
                                    }
                                } elseif ($columns->length > 0) {
                                    // Extract data for each column in the row
                                    $row_data = [];
                                    foreach ($columns as $colIndex => $column) {
                                        $row_data[$header_columns[$colIndex] ?? ""] = trim($column->nodeValue); // Map data to headers
                                    }
                                    $table_data[] = $row_data; // Add row data to the table data array
                                }
                            }
                        }
                    }

                    // Define an array to store risk factor data
                    $dataArray = [];

                    // Query the rows in the risk factors table
                    $rows = $xpath->query("//table[@class='rpt_content_table rpt_content_header rpt_content_contacts extra_info riskfactors']//tr");

                    foreach ($rows as $row) {
                        // Get the location (TransUnion, Experian, Equifax) from the first <td>
                        $location = trim($xpath->query(".//td[1]", $row)->item(0)->nodeValue);

                        // Get the risk factors (text inside <span> tags inside the second <td>)
                        $riskFactorsNodes = $xpath->query(".//td[2]//span", $row);
                        $riskFactors = [];

                        foreach ($riskFactorsNodes as $node) {
                            $riskFactors[] = trim($node->nodeValue); // Extract each risk factor
                        }

                        // If location and risk factors are found, store them in the array
                        if ($location && !empty($riskFactors)) {
                            $dataArray[] = [
                                'location' => $location,
                                'riskFactors' => $riskFactors
                            ];
                        }
                    }

                    // Initialize an object for risk factors
                    $object_riskfactors = [];
                    $object_riskfactors[''] = "Risk Factors";

                    // Process extracted risk factors
                    foreach ($dataArray as $value) {
                        $key = str_replace(":", "", $value['location']); // Remove colon from the location name
                        $data = implode(" ", $value['riskFactors']); // Combine risk factors into a single string
                        if ($key == "TransUnion" || $key == "Experian" || $key == "Equifax") {
                            $object_riskfactors[$key] = $data; // Add valid risk factors to the object
                        }
                    }

                    // Keys to check for existence
                    $keysToCheck = ['TransUnion', 'Experian', 'Equifax'];

                    // Ensure all required keys exist in $object_riskfactors
                    foreach ($keysToCheck as $key) {
                        if (!array_key_exists($key, $object_riskfactors)) {
                            $object_riskfactors[$key] = "BLANK"; // Set missing keys to "BLANK"
                        }
                    }

                    // Ensure all required keys exist in each row of $table_data
                    foreach ($table_data as &$entry) {
                        foreach ($keysToCheck as $key) {
                            if (!array_key_exists($key, $entry) || empty($entry[$key])) {
                                $entry[$key] = "BLANK"; // Set missing or empty keys to "BLANK"
                            }
                        }
                    }

                    // Append risk factors to table data
                    $table_data[] = $object_riskfactors;

                    // Output the parsed table data
                    // echo "<pre>";
                    // print_r($table_data);
                    // echo "</pre>";



                    // Prepare the INSERT query
                    $query = 
                        "INSERT INTO tbl_rating (reference, report_date, type, chester_pa, allen_tx, atlanta_ga)
                            VALUES (:reference, :report_date, :type, :chester_pa, :allen_tx, :atlanta_ga)
                        ";
                    $stmt = $pdo->prepare($query);

                    // Default report date (you can change this)
                    $report_date = date('Y-m-d');

                    $data_stored = [];

                    try {
                        // Loop through $table_data and insert each entry
                        foreach ($table_data as $row) {
                            $key = str_replace(":", "", $row['']); // Clean up location name, but allow other special characters
                            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); // Optionally sanitize to prevent XSS on output
                        
                            // For other fields, you can safely leave special characters without escaping them for SQL insertion
                            $chester_pa = $row['TransUnion'] ?? "BLANK";
                            $allen_tx = $row['Experian'] ?? "BLANK";
                            $atlanta_ga = $row['Equifax'] ?? "BLANK";
                        
                            // Execute the prepared statement
                            $stmt->execute([
                                ':reference' => $reference_value,
                                ':report_date' => $report_date,
                                ':type' => $key ?? "BLANK",
                                ':chester_pa' => $chester_pa,
                                ':allen_tx' => $allen_tx,
                                ':atlanta_ga' => $atlanta_ga,
                            ]);
                        
                            // If execution is successful, add the stored data to $data_stored
                            $data_stored[] = [
                                'reference' => $reference_value,
                                'report_date' => $report_date,
                                'type' => $key ?? "BLANK",
                                'chester_pa' => $chester_pa,
                                'allen_tx' => $allen_tx,
                                'atlanta_ga' => $atlanta_ga,
                            ];
                        }
                        

                    } catch (Exception $e) {
                        // Handle exceptions (e.g., log the error, return a failure response, etc.)
                        // return ['error' => $e->getMessage()];
                        echo json_encode(['status' => 'error', 'message' => 'There was a problem inserting data into the database. Please contact your administrator.']);
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