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
$chatroom_id = $_POST['chatroom_id'];
$password = $_POST['password'];
$whitelisted_people = $_POST['whitelisted_people'];

// Check if the chatroom already exists
$check_user_sql = "SELECT * FROM chatrooms WHERE chatroom_id = ?";
$check_user_stmt = $conn->prepare($check_user_sql);
$check_user_stmt->bind_param("s", $chatroom_id);
$check_user_stmt->execute();
$user_result = $check_user_stmt->get_result();

// It exist
if ($user_result->num_rows > 0) {
    echo "existing";
    return;
}

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
            $decrypted_whitelisted_people = explode(", ", $whitelisted_people);
            $encrypted_whitelisted_people = array();

            foreach ($decrypted_whitelisted_people as $person) {
                $encrypted_person = openssl_encrypt($person, "AES-256-CBC", $key, 0, "1234567812345678");
                array_push($encrypted_whitelisted_people, $encrypted_person);
            }
                        
            break;
        }
    }
} else {
   // echo "Encryption key file not found";
    return;
}

// Validate the input
if(empty($password)) {
    // if there isnt a password specified, return
    return;
} else {
    // hash the password with Argon2
    $options = [
        'memory_cost' => 1<<17, // 128 MB
        'time_cost' => 4,
        'threads' => 2
    ];
    $hashed_password = password_hash($password, PASSWORD_ARGON2I, $options);
    $serialized_whitelisted = serialize($encrypted_whitelisted_people);

    print_r($encrypted_whitelisted_people);

    // Prepare the SQL statement
    $sql = "INSERT INTO chatrooms (owner, chatroom_id, password, whitelisted_people)
    VALUES (?, ?, ?, ?)";

    // Bind the parameters to the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $chatroom_id, $hashed_password, $serialized_whitelisted);

    // Execute the query
    if ($stmt->execute() === TRUE) {
        echo "added";//"New record created successfully";
    } else {
        //echo "Error: " . $stmt->error;
    }
    // Close the statement
    $stmt->close();
}


?>
