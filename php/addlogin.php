<?php

// connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "baseddb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// validate the input
$display_name = trim($_POST['display_name']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($display_name) || empty($username) || empty($password)) {
        // Redirect back to the login page
        header("Location: /register.html?error=Enter all fields");
        exit;
}

// hash the password with Argon2
$options = [
    'memory_cost' => 1<<17, // 128 MB
    'time_cost' => 4,
    'threads' => 2
];
$hashed_password = password_hash($password, PASSWORD_ARGON2I, $options);

// check if the specified username already exists
$sql = "SELECT * FROM login WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Redirect back to the login page
    header("Location: /register.html?error=Username already exists");
    exit;
}

// insert the username and hashed password into the database
$sql = "INSERT INTO login (display_name, username, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $display_name, $username, $hashed_password);
$result = $stmt->execute();

if ($result === TRUE) {
    // Now add the encryption key to the key file
    // Path to the encryption_keys.txt file
    $file = $_SERVER['DOCUMENT_ROOT'] . '/encryption_keys.txt';

    // Check if the file exists, if not create it
    if (!file_exists($file)) {
        $handle = fopen($file, 'w') or die('Cannot create the file');
        fclose($handle);
    }

    // Generate a new AES key
    $key = bin2hex(random_bytes(32));

    // Append the username and key to the file
    $handle = fopen($file, 'a') or die('Cannot open the file');
    fwrite($handle, $username . ' | ' . $key . PHP_EOL);
    fclose($handle);

    // Create an entry in the userinfo table
    $sql_userinfo = "INSERT INTO userinfo (display_name) VALUES (?)";
    $stmt_userinfo = $conn->prepare($sql_userinfo);
    $stmt_userinfo->bind_param("s", $display_name);
    $stmt_userinfo->execute();

    // Redirect back to login
    error_log("A user has been created");
    header("Location: /login.html");
    exit;

} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
exit;

?>