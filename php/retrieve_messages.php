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

// Get the username from the URL
$username = $_GET['username'];

// Path to the encryption_keys.txt file
$file = $_SERVER['DOCUMENT_ROOT'] . '/encryption_keys.txt';

// Prepare the SQL query
$sql = "SELECT sender_name, recipient_name, message_text, timestamp FROM messages";
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Create an array to hold the messages
    $messages = array();

    // Iterate through the result and add each message to the array
    while($row = $result->fetch_assoc()) {
        $recipient_name = "admin";//$row['recipient_name'];
        $message = $row['message_text'];

        if ($recipient_name == $username) {
            // Check if the file exists
            if (file_exists($file)) {
                // Read the file
                $file_contents = file_get_contents($file);

                // Split the file contents into an array
                $lines = explode("\n", $file_contents);

                // Iterate through the array and get the encryption key for the recipient
                foreach ($lines as $line) {
                    $parts = explode(" - ", $line);
                    $stored_username = $parts[0];
                    $key = $parts[1];

                    if ($stored_username == $username) {
                        // Decrypt the message using the key
                        $decrypted_message = openssl_decrypt($message, "AES-256-CBC", $key, 0, "1234567812345678");

                        // Add the decrypted message to the array
                        $row['message_text'] = $decrypted_message;
                        $messages[] = $row;
                        break;
                    }
                }
            }
        }
    }

    // Convert the array to a JSON string
    $json = json_encode($messages);

} else {
    echo "0 results";
}

// Close the database connection
$conn->close();

// Return the JSON string
echo $json;
?>
