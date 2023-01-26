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
 
// Prepare the SQL query
$sql = "SELECT sender_name, message_text, timestamp FROM messages";
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Create an array to hold the messages
    $messages = array();

    // Iterate through the result and add each message to the array
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    // Convert the array to a JSON string
    $json = json_encode($messages);

    // Return the JSON string to the JavaScript code
    echo $json;
    
} else {
    echo "No messages found";
}

// Close the connection
$conn->close();

?>
