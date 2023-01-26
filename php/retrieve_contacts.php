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

// Get the user display name from the request
$user_display_name = $_POST["user_display_name"];

// Validate the input
if(empty($user_display_name)) {
    // if input is empty
    echo "user_display_name is required";
} else {
    // Prepare the SQL query
    $sql = "SELECT contact_names FROM userinfo WHERE display_name = '$user_display_name'";

    // Execute the query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo json_encode("Contacts: " . $row["contact_names"]);
        }
    } else {
        echo "No record found for user: " . $user_display_name;
    }

    // Close the connection
    $conn->close();
}
?>
