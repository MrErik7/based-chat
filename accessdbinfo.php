
<?php
session_start();

if(isset($_GET["display_name"])){
    //connect to the database
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "baseddb";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_SESSION["username"];
    $sql = "SELECT display_name FROM login WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $display_name = $row["display_name"];
            echo $display_name;
        }
    }
    $conn->close();
    exit;
}

?>