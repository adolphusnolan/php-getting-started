// Initialize an empty array to store selected project documents
array_project_documents = [];
var attachment_to_upload = "";
max_file_size = 10000000;

$(document).ready(function () {

    // When the file input with ID "doc_file" changes (i.e., when the user selects files)
    $("#doc_file").on("change", function (e) {


        hideError();

        // Variable to track whether the selected file(s) are allowed (valid format and size)
        isAllowed = false;

        var error_message = "";

        // The current `this` refers to the input element that triggered the change event
        var $input_image = $(this);

        // Retrieve the list of files selected by the user
        files_chosen_user = $input_image[0].files;
        // console.log("file is", files_chosen_user);

        // Define a regular expression to check for allowed file extensions (for various file types)
        var allowedExtensionsRegx_user =
            /(.html)$/i;

        // Loop through each selected file
        for (
            var files_size = 0;
            files_size < files_chosen_user.length;
            files_size++
        ) {
            // Get the name of the current file
            filename = files_chosen_user[files_size].name;
            // console.log("filename ", filename);

            // Extract the file extension from the filename
            user_profile_extension = filename.substr(filename.lastIndexOf("."));
            user_profile_extension = user_profile_extension.toLowerCase(); // Convert to lowercase for consistency
            // console.log("file extension:", user_profile_extension);
            // console.log("file size:", files_chosen_user[files_size].size);

            // Test if the file extension is allowed using the regular expression
            if (!allowedExtensionsRegx_user.test(user_profile_extension)) {
                // If the file extension is not allowed, mark it as invalid
                isAllowed = false;
                error_message = "Only HTML files are allowed. Please upload a valid file.";

            }

            // Check if the file size exceeds the allowed maximum file size
            else if (files_chosen_user[files_size].size > max_file_size) {
                // If the file size is too large, mark it as invalid
                isAllowed = false;
                error_message = "File size exceeds 10 MB. Please upload a smaller file.";

            }
            // If the file is valid (allowed extension and file size is within limits)
            else {
                // Mark the file as allowed
                isAllowed = true;

                // Store the file that is ready for upload
                attachment_to_upload = files_chosen_user[files_size];

            }

            if (isAllowed) {

                // console.log("Alloweded")

            } else {
                // console.log("Error")
                // console.log(error_message)

                showError(error_message)


            }
        }
    });


    // Submit the form for adding a document
    $("#btn_submit_upload_document").click(function () {
        array_project_documents = [];

        // Add the attachment to the array of project documents
        array_project_documents.push(attachment_to_upload);

        if(attachment_to_upload==""){
            showError("Please select a file to upload.");
        }
        // Check if the action is allowed (e.g., a flag or condition)
        if (isAllowed) {
            // Check if the document type field is not empty

            // Prepare data to be sent in the form of a POST request
            var postData = {
                action: "upload_new_document", // Action to be performed
            };

            // Convert the data to a JSON string for the POST request
            var dataString = JSON.stringify(postData);

            // Call the function to handle adding the document
            add_project_document(array_project_documents, dataString);

        }
    });

});

/** BEGIN FUNCTION ADD PROJECT DOCUMENT*/
async function add_project_document(file, data) {
    // console.log(file);
    let formData = new FormData();
    file.forEach(function myFunction(item, index) {
        formData.append("files[]", item);
    });
    formData.append("data", data);
    $.ajax({
        url: "actions/actions.php",
        type: "post",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            $(".loader_ring").removeClass("display-none");
        },
        success: function (response) {
            if (response["status"] == "success") {

                $("#table_section").show();
                const dataStored = response["data_stored"];
                const $tbody = $("#data_table tbody");
                
                $tbody.empty(); // Clear existing rows

                // Populate table with data
                dataStored.forEach(row => {
                    $tbody.append(`
                        <tr>
                            <td>${row.reference}</td>
                            <td>${row.report_date}</td>
                            <td>${row.type}</td>
                            <td>${row.chester_pa}</td>
                            <td>${row.allen_tx}</td>
                            <td>${row.atlanta_ga}</td>
                        </tr>
                    `);
                });

                if (dataStored.length === 0) {
                    $tbody.append(`
                        <tr>
                            <td colspan="6">No rows were inserted into the databas</td>
                        </tr>
                    `);
                }

                showNotification(response["message"],"success")
            } else {
                showNotification(response["message"],"Error")

            }
        },
        error: function (response) {
        },
        complete: function () {
            $(".loader_ring").addClass("display-none");
        },
    });
}
/** END FUNCTION ADD PROJECT DOCUMENT*/



// Function to show the notification
function showNotification(message, type = "error") {
    const notification = document.getElementById("notification");
    const messageBox = document.getElementById("notification-message");

    // Set the message and type
    messageBox.textContent = message;
    notification.className = "notification"; // Reset classes
    notification.classList.add(type === "success" ? "success" : "error");

    // Show the notification
    notification.style.display = "block";

    // Automatically hide after 5 seconds
    setTimeout(() => {
        notification.style.display = "none";
    }, 5000);
}

// Function to close the notification
function closeNotification() {
    const notification = document.getElementById("notification");
    notification.style.display = "none";
}

function showError(message){
    const fileInput = $("#doc_file");
    const errorMessage = $("#file_error_message");
    // Add error styles and show error message
    fileInput.addClass("error");
    errorMessage.text(message).show();
}

function hideError(){
    const fileInput = $("#doc_file");
    const errorMessage = $("#file_error_message");
    // Clear error styles and hide error message
    fileInput.removeClass("error");
    errorMessage.hide();
}