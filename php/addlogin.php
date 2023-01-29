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
    error_log("A user has been created");
    header("Location: /login.html");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
exit;

?>