<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New File</title>
    <style>
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            /* Enable horizontal scrolling */
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        @media (max-width: 768px) {

            th,
            td {
                padding: 10px;
                font-size: 14px;
            }

            table {
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {

            th,
            td {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>


    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 70%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input.error {
            border-color: #f44336;
            /* Red border for errors */
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        /* Notification Box Styles */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            padding: 15px;
            background-color: #f44336;
            /* Red for errors */
            color: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            font-size: 14px;
        }

        .notification.success {
            background-color: #4CAF50;
            /* Green for success */
        }

        .notification .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
    </style>

    <style>
        .form-group input,
        .form-group button {
            width: 100%;
            /* Ensure consistent width */
            padding: 10px;
            /* Same padding for both */
            border: 1px solid #ccc;
            /* Ensure the same border style */
            border-radius: 4px;
            /* Same border-radius for consistency */
            box-sizing: border-box;
            /* Ensures padding is included in width */
        }

        .form-group input[type="file"] {
            cursor: pointer;
            /* Add pointer for better UX */
        }

        .form-group button {
            background-color: #4CAF50;
            color: white;
            border: none;
            /* Remove extra border for buttons */
            font-size: 16px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Upload New File</h1>

        <!-- File File Upload -->
        <div class="form-group">
            <label for="doc_file">Upload File</label>
            <input type="file" id="doc_file" name="doc_file" required>
            <span class="error-message" id="file_error_message">Please select a valid document file.</span>
        </div>

        <!-- Save Button -->
        <div class="form-group">
            <button type="submit" id="btn_submit_upload_document">Save File</button>
        </div>

        <!-- Table to Display Data -->
        <div id="table_section" class="table-responsive" style="display: none;">
            <h3>Data has been successfully inserted into the database: </h3>
            <table id="data_table" >
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Report Date</th>
                        <th>Type</th>
                        <th>ChesterPA</th>
                        <th>AllenTX</th>
                        <th>AtlantaGA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6">No data available</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notification Box -->
    <div id="notification" class="notification">
        <button class="close-btn" onclick="closeNotification()">×</button>
        <span id="notification-message">This is an error message!</span>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="actions/ajax.js"></script>

</body>

</html>