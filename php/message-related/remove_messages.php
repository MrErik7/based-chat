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
$display_name = $_POST["display_name"];
$contact_name = $_POST["contact_name"];

// -- Check if the record exists for the user --
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
        // Remove the messages between the two users
        $sql = "DELETE FROM messages WHERE (recipient_name = '$display_name' AND sender_name = '$contact_name')
        OR (recipient_name = '$contact_name' AND sender_name = '$display_name')";

        if ($conn->query($sql) === TRUE) {
            echo "Messages between $display_name and $contact_name have been removed.";
        } else {
            echo "Error removing messages: " . $conn->error;
        }

}

// Close the statement
$check_stmt->close();

