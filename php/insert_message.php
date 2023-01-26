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

// Get the message text and sender's name from the request
$sender_name = $_POST["sender_name"];
$message_text = $_POST["message_text"];

// Validate the input
if(empty($sender_name) || empty($message_text)) {
    // if inputs are empty
    echo "Both fields are required";
} else {
    // Prepare the SQL query
    $sql = "INSERT INTO messages (sender_name, recipent_name, message_text, timestamp) VALUES ('$sender_name', 'all', '$message_text', NOW())";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // Successful
        $timestamp = $conn->query("SELECT timestamp FROM messages ORDER BY timestamp DESC LIMIT 1")->fetch_object()->timestamp;
        echo $timestamp;    
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}



?>