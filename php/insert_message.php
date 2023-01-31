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
$timestamp = $_POST["timestamp"];

// Validate the input
if(empty($sender_name) || empty($message_text)) {
    // If inputs are empty
    echo "Both fields are required";
    return;
} 

// Get the username from the URL
$username = $_GET['username'];

// Path to the encryption_keys.txt file
$file = $_SERVER['DOCUMENT_ROOT'] . '/encryption_keys.txt';

// Check if the file exists
if (file_exists($file)) {
    // Read the file
    $file_contents = file_get_contents($file);

    // Split the file contents into an array
    $lines = explode("\n", $file_contents);
    $encrypted_message = "";

    // Get the encryption key
    foreach ($lines as $line) {
        $parts = explode(" | ", $line);
        $stored_username = $parts[0];
        $key = $parts[1];

        if ($stored_username == $username) {
            $encrypted_message = openssl_encrypt($message_text, "AES-256-CBC", $key, 0, "1234567812345678");
            break;
        }
    }

    if (empty($encrypted_message)) {
        echo "Encryption key not found for the given username";
        return;
    }
} else {
    echo "Encryption key file not found";
    return;
}


// Prepare the SQL query
$sql = "INSERT INTO messages (sender_name, recipient_name, message_text, timestamp) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$encrypted_message_value = $encrypted_message;
$recipient_name = "admin";

$stmt->bind_param("ssss", $sender_name, $recipient_name, $encrypted_message_value, $timestamp);

// Execute the query
if ($stmt->execute() === TRUE) {
    // Successful
    echo "Insertion successful";
} else {
    echo "Error: " . $stmt->error;
}

// Close the connection and statement
$stmt->close();
$conn->close();
?>
