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

// Get the contacts name from the request
$contact_name = $_POST["contact_name"];
$user_display_name = $_POST["user_display_name"];

// Validate the input
if(empty($contact_name) || empty($user_display_name)) {
    // if inputs are empty
    echo "Both fields are required";
} else {
    // Prepare the SQL query
    // This line will append the contact to the current table of the database
    $sql = "UPDATE userinfo SET contact_names = CONCAT(contact_names, ', $contact_name') WHERE display_name = '$user_display_name'";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}



?>