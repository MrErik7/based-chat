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

// Get the display name and contact name from the request
$display_name = "ERIK";//$_POST["display_name"];
$contact_name = "sysadmin";//$_POST["contact_name"];

// Check if the record exists
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Record exists, check number of contact requests for user
    $num_contact_requests_sql = "SELECT COUNT(*) FROM login WHERE display_name='$display_name' AND contact_requests IS NOT NULL";
    $num_contact_requests_result = $conn->query($num_contact_requests_sql);
    $num_contact_requests_row = $num_contact_requests_result->fetch_row();
    $num_contact_requests = $num_contact_requests_row[0];

    if ($num_contact_requests > 1) {
        // There are more than one contact requests, remove the contact request
        // Retrieve current contact requests for user
        $sql = "SELECT contact_requests FROM login WHERE display_name='$display_name'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $contact_requests = $row["contact_requests"];
        } else {
            echo "No contact requests found for user $display_name.";
            $conn->close();
            exit();
        }

        // Remove contact name from list of contact requests
        $contact_requests_arr = explode(",", $contact_requests);
        $key = array_search($contact_name, $contact_requests_arr);
        if ($key !== false) {
            unset($contact_requests_arr[$key]);
        }
        $new_contact_requests = implode(",", $contact_requests_arr);

        // Update contact requests for user in database
        $sql = "UPDATE login SET contact_requests='$new_contact_requests' WHERE display_name='$display_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact request for $contact_name removed successfully from user $display_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
        
    } else {
        // This is the last contact request, replace it with null
        $sql = "UPDATE login SET contact_requests=NULL WHERE display_name='$display_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact request for $contact_name replaced with null successfully for user $display_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
    }
} else {
    echo "non-existent";
    return;
}

// Close the statement
$check_stmt->close();

