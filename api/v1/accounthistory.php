<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account History</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        /* Header Styling */
        .header {
            background-color: #007bff;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin: 0;
        }

        /* Container Styling */
        .container {
            width: 70%;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-section p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input[type="file"],
        .form-group button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
        }

        .form-group input[type="file"] {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .form-group button {
            background-color: #007bff;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        /* Table Section Styling */
        .table-section {
            margin-top: 40px;
        }

        .table-section h3 {
            text-align: center;
            font-size: 20px;
            color: #333333;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            background-color: #ffffff;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        /* Notification Styling */
        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .notification.error {
            background-color: #e74c3c;
        }

        .form-group input.error {
            border-color: #f44336;
            /* Red border for errors */
        }

        .form-group .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .notification.success {
            background-color: #2ecc71;
        }

        .notification .close-btn {
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            background: none;
            border: none;
            cursor: pointer;
            position: absolute;
            top: 5px;
            right: 10px;
        }
    </style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>

    <!-- Header Section -->
    <div class="header">
        <h1>Upload Account History File <i class="fas fa-clipboard-list"></i> </h1>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Form Section -->
        <div class="form-section">
            <p>Upload an HTML file containing your account history to import the necessary data into the database!</p>
        </div>

        <!-- File Upload Section -->
        <div class="form-group">
            <label for="doc_file">Choose File</label>
            <input type="file" id="doc_file" name="doc_file" accept=".html" required>
            <span class="error-message" id="file_error_message">Please select a valid document file.</span>

        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <button id="btn_submit_upload_document">Upload  <i class="fas fa-paperclip"></i></button>
        </div>

        <!-- Data Table Section -->
        <div class="table-section" id="table_section" style="display: none;">
            <h3>Uploaded Data</h3>
            <div class="table-responsive">
                <table id="data_table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Report Date</th>
                            <th>Furnisher</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th>ChesterPA</th>
                            <th>AllenTX</th>
                            <th>AtlantaGA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8">No data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification error">
        <button class="close-btn" onclick="closeNotification()">×</button>
        <span id="notification-message">Error: Please check the file and try again.</span>
    </div>

    <script>
        function closeNotification() {
            document.getElementById('notification').style.display = 'none';
        }
    </script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="actions/accounthistory.js"></script>

</body>

</html>
