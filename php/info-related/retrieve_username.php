<?php
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

// Get the display name from the request
$display_name = $_POST["display_name"];
echo $display_name;

// Retrieve the username from the login table
$sql = "SELECT username FROM login WHERE display_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $display_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row["username"];
    echo $username;
} else {
    echo "No user found with display name $display_name";
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>