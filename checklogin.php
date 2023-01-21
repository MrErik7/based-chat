<?php
session_start();

// The MySQL credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "baseddb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username and password from the html post 
$username = $_POST['username'];
$password = $_POST['password'];

// Get the data in the database
$sql = "SELECT password, display_name FROM login WHERE username='$username'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];
    
    if (password_verify($password, $hashed_password)) {
        // Retrieve and save the display name from the database
        $display_name = $row['display_name'];   
        $_SESSION["display_name"] = $display_name;
        // Redirect to the main page
        header("Location: index.html");
        exit;
    } else {
        echo "Invalid username or password";
    }
} else {
    echo "username or password not in db";
}
?>


