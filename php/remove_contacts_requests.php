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

// Get the display name from the request
$display_name = $_POST["display_name"];
$contact_name = $_POST["display_name"];

$contacts_requests = array();

// Check if the record exists
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Record exists, remove the contact request
    
    
} else {
    echo "non-existent";
    return;
}

// Close the statement
$check_stmt->close();
?>
