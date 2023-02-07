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
$display_name = "erik";//$_POST["display_name"];
$username = "erik";//$_POST["username"];

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
            $key = $parts[1];
            echo $key;
            break;
        }
    }
} else {
    echo "Encryption key file not found";
    return;
}

// Validate the input
if(empty($display_name)) {
    // if the display name is empty
    echo "Display name is required";
} else {
    // Check if the record exists
    $check_sql = "SELECT * FROM userinfo WHERE display_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $display_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Record exists, retrieve the encrypted contacts
        $row = $result->fetch_assoc();
        $encrypted_contacts = $row['contacts'];

        // Decrypt the contacts
        $key = hex2bin($key);
        $contacts = openssl_decrypt($encrypted_contacts, "AES-256-CBC", $key, 0, "1234567812345678");
        
            
        if ($contacts === false) {
            echo "Decryption failed" . openssl_error_string();
        } else {
            echo "Decrypted contacts: $contacts";
        }
        
        // Split the contacts into an array
        $contacts_array = explode(", ", $contacts);

        // Convert the array to a JSON string
        $json = json_encode($contacts);
    } else {
        // Record does not exist
        echo "No record found with the given display name";
    }
    
}

// Close the statement
$check_stmt->close();

// Return the JSON string
echo $json;

?>
