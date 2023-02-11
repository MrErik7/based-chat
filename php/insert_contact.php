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

// Get the info from the request
$username = $_POST['username'];
$display_name = $_POST['display_name'];
$contact_name = $_POST['contact_display_name'];

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
} else {
   // echo "Encryption key file not found";
    return;
}


// Validate the input
if(empty($contact_name) || empty($display_name)) {
    // if inputs are empty
    //echo "Both fields are required";
    return;
} else {
// Check if the record exists
$check_sql = "SELECT * FROM userinfo WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

// Fix the encrypted contact
$encrypted_contact_name_value = $encrypted_contact_name;

if ($result->num_rows > 0) {
    // Record exists, update
    $row = $result->fetch_assoc();
    $contacts = $row['contacts'];

    // Check if the contact is already added 
    // --> first decrypt the contacts
    $decrypted_contacts = array();
    $contacts_array = explode(", ", $contacts);
    foreach($contacts_array as $contact) {
        $decrypted_contact = openssl_decrypt($contact, "AES-256-CBC", $key, 0, "1234567812345678");
        array_push($decrypted_contacts, $decrypted_contact);
    }

    // Check if the contact name match any of the names in the list
    if (in_array($contact_name, $decrypted_contacts)) {
        // A match
        echo "false"; //"Contact is already added.";
        return;
    }

    $sql = "UPDATE userinfo SET contacts = CONCAT(?, ', ', '$encrypted_contact_name_value') WHERE display_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $contacts, $display_name);

} else {
    // Record does not exist, insert
    $sql = "INSERT INTO userinfo (display_name, contacts) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $display_name, $encrypted_contact_name_value);
}

// Execute the query
if ($stmt->execute() === TRUE) {
    echo "true";//"New record created successfully";
} else {
    //echo "Error: " . $stmt->error;
}
// Close the statement
$stmt->close();
}

?>