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

// Get the display_name from the URL
$display_name = $_GET['display_name'];

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
        $recipient_name = $row['recipient_name'];
        $message = $row['message_text'];
        $sender_name = $row['sender_name'];

        // Prepare the SQL query to retrieve the username
        $username_sql = "SELECT username FROM login WHERE display_name = '$sender_name'";
        $username_result = $conn->query($username_sql);

        // Check if there are any results
        if ($username_result->num_rows > 0) {
            // Get the username from the result
            $username_row = $username_result->fetch_assoc();
            $username = $username_row['username'];
        } else {
            echo "0 results";
        }

      //  echo $recipient_name;
       // echo $display_name;

        if ($recipient_name == $display_name) {
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
                    //print_r($parts[1]);

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
