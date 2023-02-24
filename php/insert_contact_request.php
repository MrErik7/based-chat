<?php
// Start the session
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "baseddb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the info from the request
$display_name = $_POST['display_name'];
$contact_name = $_POST['contact_display_name'];

// Validate so the display and contact name isnt the same 
if ($display_name == $contact_name) {
    echo "same-name";
    return;
}

// Check if the contact even exists in the database
$check_user_sql = "SELECT * FROM login WHERE display_name = ?";
$check_user_stmt = $conn->prepare($check_user_sql);
$check_user_stmt->bind_param("s", $contact_name);
$check_user_stmt->execute();
$user_result = $check_user_stmt->get_result();

// It doesnt exist
if ($user_result->num_rows == 0) {
    echo "non-existent";
    return;
}

// Validate the input
if(empty($contact_name)) {
    // if input is empty
    echo "This field is required";
    return;
} else {
    // Check if the record exists
    $check_sql = "SELECT * FROM login WHERE display_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $contact_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Record exists, get the contacts
        $row = $result->fetch_assoc();
        $contacts = $row['contacts'];

        $contacts_array = explode(", ", $contacts);

        // Check if the name of the user match any of the names in the list
        if (in_array($display_name, $contacts_array)) {
            // A match
            echo "existent"; //"Contact is already added.";
            return;
        }

        // Check if the contact request has already been sent by the user
        $contact_requests = $row['contact_requests'];
        if ($contact_requests !== 0) {
            $requests_array = explode(", ", $contact_requests);
            if (in_array($display_name, $requests_array)) {
                echo "already sent"; //"Contact request has already been sent to this user.";
                return;
            }
        }

        // Get the contact invites list from the contact
        if ($contact_requests == 0) {
            $sql = "UPDATE login SET contact_requests='$display_name' WHERE display_name=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $contact_name);   

        } else {
            $sql = "UPDATE login SET contact_requests = CONCAT(?, ', ', '$display_name') WHERE display_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $contact_requests, $contact_name);  
        }

    }

    // Execute the query
    if ($stmt->execute() === TRUE) {
        echo "sent";//"New record created successfully";
    } else {
        //echo "Error: " . $stmt->error;
    }
    // Close the statement
    $stmt->close();
}
?>
