<?php
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

// Get the data in the database using prepared statements
$sql = "SELECT password, display_name FROM login WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password'];
    
    if (password_verify($password, $hashed_password)) {
        // Retrieve and save the display name from the database
        $display_name = $row['display_name'];   

        session_start();
        $_SESSION['display_name'] = $display_name;

        // Redirect to the main page
        header("Location: /chat.html");
        exit;
    } else {
        // Redirect back to the login page
        header("Location: /login.html?error=Username or password is wrong");
        exit;

    }
} else {
    // Redirect back to the login page
    // (username is not in db)
    header("Location: /login.html?error=Username or password is wrong");
    exit;
}
$conn->close();
?>