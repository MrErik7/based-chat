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

// Get the display and user name from the request
$display_name = "sysadmin";//$_POST["display_name"];
$contacts_requests = array();

// Check if the record exists
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Record exists, retrieve the contacts
    $row = $result->fetch_assoc();
    $contacts_requests = $row['contacts_requests'];

} else {
    echo "record doesnt exist";
    return;
}

// Convert the array to a JSON string
$json = json_encode($contacts_requests);

// Return the JSON string
echo $json;

// Close the statement
$check_stmt->close();
?>
