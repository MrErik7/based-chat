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
$display_name = $_POST["display_name"];

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
            $encrypted_contact_name = openssl_encrypt($contact_name, "AES-256-CBC", $key, 0, "1234567812345678");
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


// Validate the input
if(empty($contact_name) || empty($user_display_name)) {
    // if inputs are empty
    echo "Both fields are required";
} else {
    // Prepare the SQL query with placeholders
    $sql = "UPDATE userinfo SET contact_names = CONCAT(contact_names, ', ?') WHERE display_name = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters to the placeholders
    $stmt->bind_param("ss", $encrypted_contact_name, $user_display_name);

    // Execute the query
    if ($stmt->execute() === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    // Close the connection and statement
    $stmt->close();
    $conn->close();
}
?>